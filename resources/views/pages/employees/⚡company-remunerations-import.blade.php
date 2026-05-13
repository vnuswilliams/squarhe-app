<?php

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Models\Employee;
use App\Models\Remuneration;
use Flux\Flux;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new #[Title('Import rémunérations (entreprise)')] class extends Component {
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
        Flux::modal('remun-template-modal')->show();
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
            'name' => RemunerationEnum::SUR_SALAIRE->value,
            'amount' => 100,
            'periodicity' => PeriodicityEnum::MONTHLY->value,
            'impact' => ImpactEnum::NEUTRE->value,
            'notes' => null,
        ]);

        Flux::modal('remun-template-modal')->close();

        return (new FastExcel($rows))->download('template_import_remunerations_entreprise.xlsx');
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
                'name' => $row['name'] ?? null,
                'amount' => $row['amount'] ?? null,
                'periodicity' => $row['periodicity'] ?? null,
                'impact' => $row['impact'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $employee = Employee::where('company_id', auth()->user()->company_id)->where('email', $data['employee_email'])->first();
            $validator = Validator::make($data, [
                'employee_email' => ['required', 'email'],
                'name' => ['required', Rule::in(RemunerationEnum::values())],
                'amount' => ['required', 'numeric', 'min:100'],
                'periodicity' => ['required', Rule::in(PeriodicityEnum::values())],
                'impact' => ['required', Rule::in(ImpactEnum::values())],
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

            Remuneration::create([
                'employee_id' => $employee->id,
                'name' => $row['name'],
                'type' => RemunerationEnum::from($row['name'])->type(),
                'amount' => $row['amount'],
                'periodicity' => $row['periodicity'],
                'impact' => $row['impact'],
                'notes' => $row['notes'],
                'added_by' => auth()->user()->name,
            ]);
        }

        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('Import terminé avec succès.'));
    }
}; ?>

<div class="space-y-4">
    <flux:heading size="xl">{{ __('Import rémunérations (toute l\'entreprise)') }}</flux:heading>
    <flux:button wire:click="openTemplateModal" icon="arrow-down-tray">{{ __('Télécharger le template') }}</flux:button>
    
    <flux:callout icon="information-circle" variant="info" :heading="__('Champs enum attendus')">
        <div class="text-sm space-y-1">
            <p><strong>name</strong> : {{ implode(', ', App\Enums\RemunerationEnum::values()) }}</p>
            <p><strong>periodicity</strong> : {{ implode(', ', App\Enums\PeriodicityEnum::values()) }}</p>
            <p><strong>impact</strong> : {{ implode(', ', App\Enums\ImpactEnum::values()) }}</p>
        </div>
    </flux:callout>

    <flux:input type="file" wire:model="importFile" />
    <flux:button wire:click="previewImport">{{ __('Prévisualiser') }}</flux:button>
    <flux:button variant="primary" wire:click="confirmImport" :disabled="!$readyToImport">{{ __('Importer') }}</flux:button>

    <flux:modal name="remun-template-modal" class="md:w-[640px]">
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
