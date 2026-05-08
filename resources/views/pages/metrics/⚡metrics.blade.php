<?php

use App\Models\Company;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payslip;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Métriques')] class extends Component {
    public string $selectedRef = '';

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
        if (! $this->company) {
            return [];
        }

        $refs = Payslip::query()
            ->whereHas('employee', fn ($query) => $query->where('company_id', $this->company->id))
            ->whereNotNull('ref')
            ->pluck('ref')
            ->filter()
            ->unique()
            ->values()
            ->all();

        usort($refs, function (string $a, string $b): int {
            return Carbon::createFromFormat('m-Y', $b)->timestamp <=> Carbon::createFromFormat('m-Y', $a)->timestamp;
        });

        return $refs;
    }

    #[Computed]
    public function previousRef(): ?string
    {
        if (! $this->selectedRef) {
            return null;
        }

        return Carbon::createFromFormat('m-Y', $this->selectedRef)->subMonth()->format('m-Y');
    }

    #[Computed]
    public function metricsRows(): array
    {
        $current = $this->metricsForRef($this->selectedRef);
        $previous = $this->metricsForRef($this->previousRef);

        return [
            $this->makeRow(__('Bulletins de paie générés'), $current['payslips_count'], $previous['payslips_count']),
            $this->makeRow(__('Salaire brut total'), $current['gross_salary_total'], $previous['gross_salary_total']),
            $this->makeRow(__('Net à payer total'), $current['net_to_pay_total'], $previous['net_to_pay_total']),
            $this->makeRow(__('Heures supplémentaires totales'), $current['overtime_hours_total'], $previous['overtime_hours_total']),
            $this->makeRow(__('Jours de congés totaux'), $current['leave_days_total'], $previous['leave_days_total']),
        ];
    }

    protected function metricsForRef(?string $ref): array
    {
        if (! $this->company || ! $ref) {
            return $this->emptyMetrics();
        }

        $snapshot = $this->snapshotForRef($ref);

        if (! empty($snapshot)) {
            return array_merge($this->emptyMetrics(), $snapshot);
        }

        $payslips = Payslip::query()
            ->where('ref', $ref)
            ->whereHas('employee', fn ($query) => $query->where('company_id', $this->company->id))
            ->get();

        $gross = 0.0;
        $netToPay = 0.0;

        foreach ($payslips as $payslip) {
            $formattedSalaries = $payslip->formatted_salaries ?? [];

            $gross += (float) data_get($formattedSalaries, 'gross_salary.amount', 0);
            $netToPay += (float) data_get($formattedSalaries, 'nap.amount', 0);
        }

        return [
            'payslips_count' => $payslips->count(),
            'gross_salary_total' => $gross,
            'net_to_pay_total' => $netToPay,
            'overtime_hours_total' => (float) Overtime::query()
                ->where('ref', $ref)
                ->whereHas('employee', fn ($query) => $query->where('company_id', $this->company->id))
                ->sum('hours'),
            'leave_days_total' => (float) Leave::query()
                ->where('ref', $ref)
                ->whereHas('employee', fn ($query) => $query->where('company_id', $this->company->id))
                ->sum('days'),
        ];
    }

    protected function snapshotForRef(string $ref): array
    {
        if (! $this->company || ! method_exists($this->company, 'payrollClosures')) {
            return [];
        }

        $closure = $this->company->payrollClosures()->where('ref', $ref)->latest('id')->first();

        if (! $closure || ! method_exists($closure, 'snapshots')) {
            return [];
        }

        $snapshot = $closure->snapshots()->where('ref', $ref)->latest('id')->first();

        if (! $snapshot) {
            return [];
        }

        return [
            'payslips_count' => (float) data_get($snapshot->data, 'payslips_count', 0),
            'gross_salary_total' => (float) data_get($snapshot->data, 'gross_salary_total', 0),
            'net_to_pay_total' => (float) data_get($snapshot->data, 'net_to_pay_total', 0),
            'overtime_hours_total' => (float) data_get($snapshot->data, 'overtime_hours_total', 0),
            'leave_days_total' => (float) data_get($snapshot->data, 'leave_days_total', 0),
        ];
    }

    protected function makeRow(string $label, float|int $current, float|int $previous): array
    {
        $delta = $current - $previous;
        $variation = $previous === 0 ? null : ($delta / $previous) * 100;

        return compact('label', 'current', 'previous', 'delta', 'variation');
    }

    protected function emptyMetrics(): array
    {
        return [
            'payslips_count' => 0,
            'gross_salary_total' => 0,
            'net_to_pay_total' => 0,
            'overtime_hours_total' => 0,
            'leave_days_total' => 0,
        ];
    }

    public function variationBadgeColor(float|int $delta): string
    {
        return $delta > 0 ? 'green' : ($delta < 0 ? 'red' : 'zinc');
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading level="1" class="font-bold">{{ __('Métriques') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                {{ __('Comparaison des données de paie entre le mois N et le mois N-1.') }}
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
        <flux:text>{{ __('Comparaison : :current vs :previous.', ['current' => $selectedRef ?: 'N/A', 'previous' => $this->previousRef ?: 'N/A']) }}</flux:text>
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
                @foreach ($this->metricsRows as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-200">{{ number_format($row['current'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-200">{{ number_format($row['previous'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-sm"><flux:badge color="{{ $this->variationBadgeColor($row['delta']) }}">{{ number_format($row['delta'], 2, ',', ' ') }}</flux:badge></td>
                        <td class="px-4 py-3 text-right text-sm text-zinc-700 dark:text-zinc-200">{{ is_null($row['variation']) ? '—' : number_format($row['variation'], 2, ',', ' ') . ' %' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
