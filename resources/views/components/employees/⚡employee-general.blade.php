<?php

use App\Jobs\calculateImpotForEmployee;
use App\Models\Employee;
use App\Enums\CivilityEnum;
use App\Enums\NationalityEnum;
use App\Enums\StatusEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\RemunerationTypeEnum;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public Employee $employee;
    public bool $syndicat = false;

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
        $this->syndicat = $this->employee->data['syndicat'] ?? false;
    }

    public function updatedSyndicat()
    {
        // Update the employee data
        $data = $this->employee->data ?? [];
        $data['syndicat'] = $this->syndicat;
        $this->employee->data = $data;
        $this->employee->save();

        calculateImpotForEmployee::dispatch($this->employee);
        Flux::toast(variant: "success", text: 'Veuillez patienter pour la prise en compte du syndicat..');
    }
};
?>

<div class="space-y-8">

    <x-container>
        <flux:switch label="Syndicat" description="{{ __('L\'employé fait-il partie d\'un syndicat ?') }}" wire:model.live="syndicat" />
    </x-container>

@php
    $salary = $employee->salary;
    $grossSalary = (float) ($salary?->gross_salary ?? 0);
    $netSalary = (float) ($salary?->nap ?? 0);

    $employeeContributionTotal = (float) ($employee->employeeContributions?->total ?? 0);
    $employerContributionTotal = (float) ($employee->employerContributions?->total ?? 0);

    $totalEmployerCost = $grossSalary + $employerContributionTotal;

    $bonusAndAllowance = (float) $employee->remunerations
        ->whereIn('type', [
            RemunerationTypeEnum::PRIME->value,
            RemunerationTypeEnum::INDEMNITE->value,
        ])
        ->sum('amount');

    $formatMoney = fn (float $value): string => number_format($value, 0, ',', ' ') . ' F CFA';
@endphp

    <x-container>
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="lg">{{ __('Informations Générales') }}</flux:heading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Personnel -->
            <div class="space-y-4">
                <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-400">{{ __('Détails Personnels') }}</flux:heading>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Civilité') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->civility ? CivilityEnum::tryFrom($employee->civility)?->label() : 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Nom Complet') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->name }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Date de Naissance') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->bday ? \Carbon\Carbon::parse($employee->bday)->format('d/m/Y') : 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Nationalité') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->nationality ? NationalityEnum::tryFrom($employee->nationality)?->label() : 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Nombre d\'enfants') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->child ?? 0 }}</flux:text>
                </div>
            </div>

            <!-- Contact & Identifiants -->
            <div class="space-y-4">
                <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-400">{{ __('Contact & Identifiants') }}</flux:heading>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Email') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->email ?? 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Téléphone') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->phone ?? 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('NIU') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->niu ?? 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Numéro CNPS') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->cnps ?? 'N/A' }}</flux:text>
                </div>
            </div>

            <!-- Professionnel -->
            <div class="space-y-4">
                <flux:heading level="3" size="sm" class="uppercase tracking-wider text-zinc-400">{{ __('Détails Professionnels') }}</flux:heading>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Département') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->department ?? 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Poste') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->job_title ?? 'N/A' }}</flux:text>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Type de Contrat') }}</flux:text>
                    <flux:badge size="sm" color="zinc">
                        {{ ContractTypeEnum::tryFrom($employee->contract_type)?->label() ?? 'N/A' }}
                    </flux:badge>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Statut') }}</flux:text>
                    <flux:badge size="sm" color="{{ $employee->status?->color() ?? 'zinc' }}">
                        {{ $employee->status?->label() ?? 'N/A' }}
                    </flux:badge>
                </div>

                <div class="space-y-1">
                    <flux:text variant="subtle" size="sm">{{ __('Date d\'embauche') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->start_date ? $employee->start_date->format('d/m/Y') : 'N/A' }}</flux:text>
                </div>
            </div>
        </div>
    </x-container>

    <x-container>
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="lg">{{ __('Informations salariales') }}</flux:heading>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:text variant="subtle" size="sm">{{ __('Masse salariale') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($grossSalary) }}</flux:text>
            </div>

            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:text variant="subtle" size="sm">{{ __('Salaire brut') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($grossSalary) }}</flux:text>
            </div>

            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:text variant="subtle" size="sm">{{ __('Salaire net') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($netSalary) }}</flux:text>
            </div>

            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:text variant="subtle" size="sm">{{ __('Charge patronale') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($employerContributionTotal) }}</flux:text>
            </div>

            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:text variant="subtle" size="sm">{{ __('Charge salariale') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($employeeContributionTotal) }}</flux:text>
            </div>

            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:text variant="subtle" size="sm">{{ __('Coût total employeur') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($totalEmployerCost) }}</flux:text>
            </div>

            <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/30 md:col-span-2 lg:col-span-3">
                <flux:text variant="subtle" size="sm">{{ __('Prime + indemnités') }}</flux:text>
                <flux:text class="text-lg font-semibold">{{ $formatMoney($bonusAndAllowance) }}</flux:text>
            </div>
        </div>
    </x-container>
</div>
