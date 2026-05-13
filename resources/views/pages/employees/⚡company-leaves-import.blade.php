<?php

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\Leave;
use App\Services\CalculateDays;
use Flux\Flux;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new #[Title('Import congés (entreprise)')] class extends Component {
    use WithFileUploads;

    public $importFile;
    public array $previewRows = [];
    public array $importErrors = [];
    public bool $readyToImport = false;
    public array $selectedEmployeeIds = [];

    #[Computed]
    public function employees()
    {
        return Employee::query()->where('company_id', auth()->user()->company_id)->orderBy('name')->get(['id', 'name', 'email']);
    }

    public function openTemplateModal(): void
    {
        $this->selectedEmployeeIds = [];
        Flux::modal('leaves-template-modal')->show();
    }

    public function downloadTemplate()
    {
        $employees = $this->employees->whereIn('id', $this->selectedEmployeeIds);

        if ($employees->isEmpty()) {
            Flux::toast(variant: 'warning', text: __('Veuillez sélectionner au moins un employé.'));
            return;
        }

        $rows = $employees->map(fn ($employee) => [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'employee_email' => $employee->email,
            'type' => LeaveTypeEnum::ANNUAL->value,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'notes' => null,
        ]);

        Flux::modal('leaves-template-modal')->close();

        return (new FastExcel($rows))->download('template_import_conges_entreprise.xlsx');
    }

    public function previewImport(): void
    {
        $this->validate(['importFile' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120']]);
        $rows = (new FastExcel)->import($this->importFile->getRealPath());
        $this->previewRows = [];
        $this->importErrors = [];

        foreach ($rows as $index => $row) {
            $data = [
                'employee_email' => $row['employee_email'] ?? null,
                'type' => $row['type'] ?? null,
                'start_date' => $row['start_date'] ?? null,
                'end_date' => $row['end_date'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $employee = Employee::where('company_id', auth()->user()->company_id)->where('email', $data['employee_email'])->first();
            $validator = Validator::make($data, [
                'employee_email' => ['required', 'email'],
                'type' => ['required', Rule::in(LeaveTypeEnum::values())],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'notes' => ['nullable', 'string', 'max:100'],
            ]);

            if (! $employee) {
                $this->importErrors[] = ['line' => $index + 2, 'errors' => [__('Employé introuvable dans votre entreprise.')]];
            }
            if ($validator->fails()) {
                $this->importErrors[] = ['line' => $index + 2, 'errors' => $validator->errors()->all()];
            }

            $this->previewRows[] = $data;
        }

        $this->readyToImport = count($this->importErrors) === 0 && count($this->previewRows) > 0;
    }

    public function confirmImport(): void
    {
        if (! $this->readyToImport) {
            Flux::toast(variant: 'danger', text: __('Corrigez les erreurs avant import.'));
            return;
        }

        foreach ($this->previewRows as $row) {
            $employee = Employee::where('company_id', auth()->user()->company_id)->where('email', $row['employee_email'])->first();
            if (! $employee) continue;

            Leave::create([
                'employee_id' => $employee->id,
                'type' => $row['type'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'days' => app(CalculateDays::class)->calculateDays($row['start_date'], $row['end_date']),
                'status' => StatusEnum::APPROVED->value,
                'notes' => $row['notes'],
            ]);
        }

        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('Import terminé avec succès.'));
    }
}; ?>

<div class="space-y-4">
    <flux:heading size="xl">{{ __('Import congés (toute l\'entreprise)') }}</flux:heading>
    <flux:button wire:click="openTemplateModal" icon="arrow-down-tray">{{ __('Télécharger le template') }}</flux:button>
    
    <flux:callout icon="information-circle" variant="info" :heading="__('Champs enum attendus')">
        <div class="text-sm space-y-1">
            <p><strong>type</strong> : {{ implode(', ', App\Enums\LeaveTypeEnum::values()) }}</p>
        </div>
    </flux:callout>

    <flux:input type="file" wire:model="importFile" />
    <flux:button wire:click="previewImport">{{ __('Prévisualiser') }}</flux:button>
    <flux:button variant="primary" wire:click="confirmImport" :disabled="!$readyToImport">{{ __('Importer') }}</flux:button>

    <flux:modal name="leaves-template-modal" class="md:w-[640px]">
        <div class="space-y-4">
            <flux:heading>{{ __('Sélectionner les employés') }}</flux:heading>
            <div class="max-h-72 space-y-2 overflow-y-auto">
                @foreach ($this->employees as $employee)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="selectedEmployeeIds" value="{{ $employee->id }}">
                        <span>{{ $employee->name }} ({{ $employee->id }})</span>
                    </label>
                @endforeach
            </div>
            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="downloadTemplate">{{ __('Générer le template') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
