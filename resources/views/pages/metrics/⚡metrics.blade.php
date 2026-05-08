<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payslip;
use App\Models\Remuneration;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Métriques')] class extends Component {
    public string $selectedRef;

    public function mount(): void
    {
        $this->selectedRef = $this->availableRefs[0] ?? now()->format('m-Y');
    }

    #[Computed]
    public function company(): ?Company
    {
        return auth()->user()?->companies()->first();
    }

    #[Computed]
    public function availableRefs(): array
    {
        $refs = Payslip::query()
            ->when($this->company, fn ($query) => $query->where('company_id', $this->company->id))
            ->whereNotNull('ref')
            ->pluck('ref')
            ->filter()
            ->unique()
            ->values()
            ->all();

        usort($refs, function (string $a, string $b) {
            $aDate = Carbon::createFromFormat('m-Y', $a);
            $bDate = Carbon::createFromFormat('m-Y', $b);

            return $bDate->timestamp <=> $aDate->timestamp;
        });

        return $refs;
    }

    #[Computed]
    public function previousRef(): ?string
    {
        $current = Carbon::createFromFormat('m-Y', $this->selectedRef);

        return $current->subMonth()->format('m-Y');
    }

    #[Computed]
    public function metricsRows(): array
    {
        $current = $this->collectMetrics($this->selectedRef);
        $previous = $this->collectMetrics($this->previousRef);

        return [
            $this->row(__('Employés actifs'), $current['active_employees'], $previous['active_employees']),
            $this->row(__('Employés avec bulletin'), $current['employees_with_payslip'], $previous['employees_with_payslip']),
            $this->row(__('Salaire brut (FCFA)'), $current['gross_salary'], $previous['gross_salary']),
            $this->row(__('Salaire net (FCFA)'), $current['net_salary'], $previous['net_salary']),
            $this->row(__('Heures supplémentaires'), $current['overtime_hours'], $previous['overtime_hours']),
            $this->row(__('Congés (jours)'), $current['leave_days'], $previous['leave_days']),
        ];
    }

    protected function collectMetrics(?string $ref): array
    {
        if (! $this->company || ! $ref) {
            return $this->emptyMetrics();
        }

        $snapshot = $this->snapshotMetricsForRef($ref);

        if (! empty($snapshot)) {
            return array_merge($this->emptyMetrics(), $snapshot);
        }

        return [
            'active_employees' => Employee::query()->where('company_id', $this->company->id)->active()->count(),
            'employees_with_payslip' => Payslip::query()->where('company_id', $this->company->id)->where('ref', $ref)->count(),
            'gross_salary' => (float) Remuneration::query()->where('company_id', $this->company->id)->where('ref', $ref)->sum('salaire_brut'),
            'net_salary' => (float) Payslip::query()->where('company_id', $this->company->id)->where('ref', $ref)->sum('salary_to_be_paid'),
            'overtime_hours' => (float) Overtime::query()->where('company_id', $this->company->id)->where('ref', $ref)->sum('hour_weekday')
                + (float) Overtime::query()->where('company_id', $this->company->id)->where('ref', $ref)->sum('hour_holiday'),
            'leave_days' => (float) Leave::query()->where('company_id', $this->company->id)->where('ref', $ref)->sum('duration'),
        ];
    }

    protected function snapshotMetricsForRef(string $ref): array
    {
        if (! $this->company || ! method_exists($this->company, 'payrollClosures')) {
            return [];
        }

        $closure = $this->company->payrollClosures()
            ->where('ref', $ref)
            ->latest('id')
            ->first();

        if (! $closure || ! method_exists($closure, 'snapshots')) {
            return [];
        }

        $snapshot = $closure->snapshots()->where('ref', $ref)->latest('id')->first();

        if (! $snapshot) {
            return [];
        }

        return [
            'active_employees' => (int) Arr::get($snapshot->data, 'active_employees', 0),
            'employees_with_payslip' => (int) Arr::get($snapshot->data, 'employees_with_payslip', 0),
            'gross_salary' => (float) Arr::get($snapshot->data, 'gross_salary', 0),
            'net_salary' => (float) Arr::get($snapshot->data, 'net_salary', 0),
            'overtime_hours' => (float) Arr::get($snapshot->data, 'overtime_hours', 0),
            'leave_days' => (float) Arr::get($snapshot->data, 'leave_days', 0),
        ];
    }

    protected function row(string $label, float|int $current, float|int $previous): array
    {
        $delta = $current - $previous;
        $variation = $previous == 0 ? null : ($delta / $previous) * 100;

        return compact('label', 'current', 'previous', 'delta', 'variation');
    }

    protected function emptyMetrics(): array
    {
        return [
            'active_employees' => 0,
            'employees_with_payslip' => 0,
            'gross_salary' => 0,
            'net_salary' => 0,
            'overtime_hours' => 0,
            'leave_days' => 0,
        ];
    }

    public function variationBadgeColor(float|int $delta): string
    {
        if ($delta > 0) {
            return 'green';
        }

        if ($delta < 0) {
            return 'red';
        }

        return 'zinc';
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading level="1" class="font-bold">{{ __('Métriques') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                {{ __('Comparez les données actuelles et les snapshots de paie par mois (N vs N-1).') }}
            </flux:text>
        </div>

        <div class="w-full md:w-64">
            <flux:field>
                <flux:label>{{ __('Mois de référence') }}</flux:label>
                <flux:select wire:model.live="selectedRef">
                    @foreach ($this->availableRefs as $ref)
                        <flux:select.option value="{{ $ref }}">{{ $ref }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:text>
            {{ __('Comparaison du mois :current avec :previous.', ['current' => $selectedRef, 'previous' => $this->previousRef ?? __('N/A')]) }}
        </flux:text>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">{{ __('Indicateur') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">{{ __('Mois N') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">{{ __('Mois N-1') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">{{ __('Écart') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">{{ __('Variation') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->metricsRows as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-200">{{ number_format($row['current'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-200">{{ number_format($row['previous'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <flux:badge color="{{ $this->variationBadgeColor($row['delta']) }}">
                                {{ number_format($row['delta'], 2, ',', ' ') }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-200">
                            @if (is_null($row['variation']))
                                —
                            @else
                                {{ number_format($row['variation'], 2, ',', ' ') }} %
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-300">
                            {{ __('Aucune donnée métrique disponible pour le moment.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
