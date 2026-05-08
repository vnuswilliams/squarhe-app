<?php

use App\Enums\RemunerationTypeEnum;
use Livewire\Component;

new class extends Component {
    public $company;

   

    public function with(): array
    {
        $employees = $this->company->payrollEmployees()
        ->with([
            'salary',
            'employeeContributions',
            'employerContributions',
            'remunerations',
        ])->get();

        $grossSalaries = (float) $employees->sum(fn ($employee) => (float) ($employee->salary?->gross_salary ?? 0));
        $netSalaries = (float) $employees->sum(fn ($employee) => (float) ($employee->salary?->nap ?? 0));
        $employeeCharges = (float) $employees->sum(fn ($employee) => (float) ($employee->employeeContributions?->total ?? 0));
        $employerCharges = (float) $employees->sum(fn ($employee) => (float) ($employee->employerContributions?->total ?? 0));

        $bonusAndAllowances = (float) $employees->sum(function ($employee) {
            return (float) $employee->remunerations
                ->whereIn('type', [
                    RemunerationTypeEnum::PRIME->value,
                    RemunerationTypeEnum::INDEMNITE->value,
                ])
                ->sum('amount');
        });

        return [
            'employeesCount' => $employees->count(),
            'payrollMass' => $grossSalaries,
            'grossSalaries' => $grossSalaries,
            'netSalaries' => $netSalaries,
            'employeeCharges' => $employeeCharges,
            'employerCharges' => $employerCharges,
            'totalEmployerCost' => $grossSalaries + $employerCharges,
            'bonusAndAllowances' => $bonusAndAllowances,
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <flux:heading size="lg">{{ __('Vue générale de la paie') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Indicateurs globaux pour tous les employés de l’entreprise.') }}</flux:text>
        </div>

        <flux:badge color="zinc">{{ __('Employés :count', ['count' => $employeesCount]) }}</flux:badge>
    </div>

    @php
        $formatMoney = fn (float $value): string => number_format($value, 0, ',', ' ') . ' F CFA';
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:text variant="subtle" size="sm">{{ __('Masse salariale') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($payrollMass) }}</flux:text>
        </div>

        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:text variant="subtle" size="sm">{{ __('Salaire brut') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($grossSalaries) }}</flux:text>
        </div>

        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:text variant="subtle" size="sm">{{ __('Salaire net') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($netSalaries) }}</flux:text>
        </div>

        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:text variant="subtle" size="sm">{{ __('Charge patronale') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($employerCharges) }}</flux:text>
        </div>

        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:text variant="subtle" size="sm">{{ __('Charge salariale') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($employeeCharges) }}</flux:text>
        </div>

        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
            <flux:text variant="subtle" size="sm">{{ __('Coût total employeur') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($totalEmployerCost) }}</flux:text>
        </div>

        <div class="space-y-1 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-900/40 md:col-span-2 xl:col-span-3">
            <flux:text variant="subtle" size="sm">{{ __('Prime + indemnités') }}</flux:text>
            <flux:text class="text-lg font-semibold">{{ $formatMoney($bonusAndAllowances) }}</flux:text>
        </div>
    </div>
</div>
