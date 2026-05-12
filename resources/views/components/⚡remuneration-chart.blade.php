<?php
// app/Livewire/RemunerationChart.php

namespace App\Livewire;

use App\Models\Remuneration;
use Livewire\Component;

new class extends Component
{
    public string $employeeId;
    public string $view = 'bar';    // bar | line | doughnut
    public string $groupBy = 'ref'; // ref (mois) | name (type prime)

    public function mount(string $employeeId): void
    {
        $this->employeeId = $employeeId;
    }

    // ─── Données brutes ───────────────────────────────────────────────

    private function rawData(): \Illuminate\Support\Collection
    {
        return Remuneration::where('employee_id', $this->employeeId)
            ->orderBy('ref')
            ->get(['ref', 'name', 'amount', 'impact']);
    }

    // ─── Formatage pour Chart.js ──────────────────────────────────────

    public function chartData(): array
    {
        $data = $this->rawData();

        if ($this->groupBy === 'ref') {
            // Montant total par mois (ref = "MM-YYYY")
            $grouped = $data->groupBy('ref')->map(
                fn ($items) => $items->sum('amount')
            );

            return [
                'labels'   => $grouped->keys()->toArray(),
                'datasets' => [[
                    'label'           => 'Total rémunérations (FCFA)',
                    'data'            => $grouped->values()->toArray(),
                    'backgroundColor' => '#6366f1',
                    'borderColor'     => '#4f46e5',
                    'borderWidth'     => 2,
                    'borderRadius'    => 6,
                ]],
            ];
        }

        // Montant total par type (name enum)
        $grouped = $data->groupBy(fn ($r) => $r->name->label())
                        ->map(fn ($items) => $items->sum('amount'));

        $palette = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#a855f7'];

        return [
            'labels'   => $grouped->keys()->toArray(),
            'datasets' => [[
                'label'           => 'Montant par type (FCFA)',
                'data'            => $grouped->values()->toArray(),
                'backgroundColor' => array_slice($palette, 0, $grouped->count()),
                'borderWidth'     => 1,
                'borderRadius'    => 6,
            ]],
        ];
    }

    // ─── Stats résumées ───────────────────────────────────────────────

    public function stats(): array
    {
        $data = $this->rawData();

        return [
            'total'   => number_format($data->sum('amount'), 0, ',', ' '),
            'count'   => $data->count(),
            'moyenne' => $data->count()
                ? number_format($data->avg('amount'), 0, ',', ' ')
                : 0,
            'max'     => number_format($data->max('amount'), 0, ',', ' '),
        ];
    }

    // ─── Mise à jour réactive ─────────────────────────────────────────

    public function updatedView(): void
    {
        $this->pushChartUpdate();
    }

    public function updatedGroupBy(): void
    {
        $this->pushChartUpdate();
    }

    private function pushChartUpdate(): void
    {
        $this->dispatch('remuneration-chart-updated', chartData: $this->chartData());
    }

    // ─── Render ───────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.remuneration-chart', [
            'initialChartData' => $this->chartData(),
            'stats'            => $this->stats(),
        ]);
    }
}

?>

<div>
    {{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
</div>