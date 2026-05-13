<?php

use App\Enums\HsuppEnum;
use App\Models\Employee;
use App\Models\Overtime;
use App\Services\CalculateHsupp;
use Flux\Flux;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new #[Title('Import heures supp (entreprise)')] class extends Component {
    use WithFileUploads;

    public $importFile;
    public array $previewRows = [];
    public array $importErrors = [];
    public bool $readyToImport = false;
    public array $selectedEmployeeIds = [];

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function openTemplateModal(): void
    {
        $this->selectedEmployeeIds = [];
        Flux::modal('overtime-template-modal')->show();
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
            'day_type' => HsuppEnum::WORKINGDAY->value,
            'hours' => 1,
            'hours_rate' => null,
            'week' => 1,
            'notes' => null,
        ]);

        Flux::modal('overtime-template-modal')->close();

        return (new FastExcel($rows))->download('template_import_heures_supp_entreprise.xlsx');
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
                'day_type' => $row['day_type'] ?? null,
                'hours' => $row['hours'] ?? null,
                'hours_rate' => $row['hours_rate'] ?? null,
                'week' => $row['week'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $employee = Employee::query()->where('company_id', auth()->user()->company_id)->where('email', $data['employee_email'])->first();
            $validator = Validator::make($data, [
                'employee_email' => ['required', 'email'],
                'day_type' => ['required', Rule::in(HsuppEnum::values())],
                'hours' => ['required', 'numeric', 'min:1'],
                'hours_rate' => ['nullable', 'numeric', 'min:1'],
                'week' => ['required', 'numeric', 'regex:/^[1-5]$/'],
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
            $employee = Employee::query()->where('company_id', auth()->user()->company_id)->where('email', $row['employee_email'])->first();
            if (! $employee) continue;

            Overtime::create([
                'employee_id' => $employee->id,
                'day_type' => $row['day_type'],
                'hours' => $row['hours'],
                'hours_rate' => $row['hours_rate'] ?: app(CalculateHsupp::class)->hourRate($employee),
                'week' => $row['week'],
                'notes' => $row['notes'],
                'multiplier' => HsuppEnum::from($row['day_type'])->dayType(),
                'added_by' => auth()->user()->name,
            ]);
        }

        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('Import terminé avec succès.'));
    }
}; ?>

<div class="space-y-4">
    <flux:heading size="xl">{{ __('Import heures supplémentaires (toute l\'entreprise)') }}</flux:heading>
    <flux:button wire:click="openTemplateModal" icon="arrow-down-tray">{{ __('Télécharger le template') }}</flux:button>
    
    <flux:callout icon="information-circle" variant="info" :heading="__('Champs enum attendus')">
        <div class="text-sm space-y-1">
            <p><strong>day_type</strong> : {{ implode(', ', App\Enums\HsuppEnum::values()) }}</p>
            <p><strong>week</strong> : 1, 2, 3, 4, 5</p>
        </div>
    </flux:callout>

    <flux:input type="file" wire:model="importFile" />
    <flux:button wire:click="previewImport">{{ __('Prévisualiser') }}</flux:button>
    <flux:button variant="primary" wire:click="confirmImport" :disabled="!$readyToImport">{{ __('Importer') }}</flux:button>

    <flux:modal name="overtime-template-modal" class="md:w-[640px]">
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
