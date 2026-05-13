<?php
// app/Livewire/Dashboard/PayrollOverviewChart.php

namespace App\Livewire\Dashboard;

use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Remuneration;
use Livewire\Component;

new class  extends Component
{
    public string $ref     = '';
    public string $module  = 'remunerations'; // remunerations | leaves | overtimes
    public string $groupBy = 'employee';
    public int    $limit   = 10;

    public function mount(): void
    {
        $this->ref = now()->format('m-Y');
    }

    // ─── Mois disponibles ─────────────────────────────────────────────

    public function availableRefs(): array
    {
        $model = match ($this->module) {
            'leaves'    => Leave::class,
            'overtimes' => Overtime::class,
            default     => Remuneration::class,
        };

        return $model::selectRaw('ref')
            ->groupBy('ref')
            ->orderByDesc('ref')
            ->limit(12)
            ->pluck('ref')
            ->toArray();
    }

    // ─── Dispatcher central ───────────────────────────────────────────

    public function chartData(): array
    {
        return match ($this->module) {
            'leaves'    => $this->leavesChartData(),
            'overtimes' => $this->overtimesChartData(),
            default     => $this->remunerationsChartData(),
        };
    }

    public function stats(): array
    {
        return match ($this->module) {
            'leaves'    => $this->leavesStats(),
            'overtimes' => $this->overtimesStats(),
            default     => $this->remunerationsStats(),
        };
    }

    // ─── Rémunérations ────────────────────────────────────────────────

    private function remunerationsChartData(): array
    {
        $query = Remuneration::whereRef( $this->ref);

        if ($this->groupBy === 'employee') {
            $rows = $query
                ->selectRaw('employee_id, SUM(amount) as total')
                ->groupBy('employee_id')
                ->with('employee:id,name')
                ->orderByDesc('total')
                ->limit($this->limit)
                ->get();

            $labels = $rows->map(fn ($r) => $r->employee?->name ?? '—')->toArray();
            $data   = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();
            } else {
                $rows = $query->selectRaw("type, SUM(amount) as total")->groupBy("type")->orderBy("type")->get();
    
                $labels = $rows->map(fn($r) => $r->type->label())->toArray();
                $data = $rows->pluck("total")->map(fn($v) => (int) $v)->toArray();
            }

        return $this->buildDataset($labels, $data, "Rémunérations — {$this->ref}", 'indigo');
    }

    private function remunerationsStats(): array
    {
        $q = Remuneration::whereRef( $this->ref);
        return [
            ['label' => 'Masse totale',  'value' => number_format($q->sum('amount'), 0, ',', ' ') . ' F', 'icon' => '💰'],
            ['label' => 'Employés',      'value' => $q->distinct('employee_id')->count('employee_id'),     'icon' => '👥'],
            ['label' => 'Entrées',       'value' => $q->count(),                                          'icon' => '📋'],
            ['label' => 'Moyenne',       'value' => number_format($q->count() ? $q->avg('amount') : 0, 0, ',', ' ') . ' F', 'icon' => '📊'],
        ];
    }

    // ─── Congés ───────────────────────────────────────────────────────

    private function leavesChartData(): array
    {
        $query = Leave::whereRef( $this->ref);

        if ($this->groupBy === 'employee') {
            $rows = $query
                ->selectRaw('employee_id, SUM(days) as total')
                ->groupBy('employee_id')
                ->with('employee:id,name')
                ->orderByDesc('total')
                ->limit($this->limit)
                ->get();

            $labels = $rows->map(fn ($r) => $r->employee?->name ?? '—')->toArray();
            $data   = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();
        } else {
            // Grouper par type de congé
            $rows = $query
                ->selectRaw('type, SUM(days) as total, COUNT(*) as nb')
                ->groupBy('type')
                ->orderByDesc('total')
                ->get();

            $labels = $rows->map(fn ($r) => $r->type->label())->toArray();
            $data   = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();
        }

        return $this->buildDataset($labels, $data, "Jours de congés — {$this->ref}", 'rose');
    }

    private function leavesStats(): array
    {
        $q = Leave::whereRef( $this->ref);
        return [
            ['label' => 'Total jours',   'value' => $q->sum('days'),                                  'icon' => '🗓️'],
            ['label' => 'Employés',      'value' => $q->distinct('employee_id')->count('employee_id'), 'icon' => '👥'],
            ['label' => 'Demandes',      'value' => $q->count(),                                       'icon' => '📋'],
            ['label' => 'Moy. par agent','value' => round($q->count() ? $q->avg('days') : 0, 1) . ' j','icon' => '📊'],
        ];
    }

    // ─── Heures supp. ─────────────────────────────────────────────────

    private function overtimesChartData(): array
    {
        $query = Overtime::whereRef( $this->ref);

        if ($this->groupBy === 'employee') {
            $rows = $query
                ->selectRaw('employee_id, SUM(hours) as total_hours, SUM(alloc) as total_alloc')
                ->groupBy('employee_id')
                ->with('employee:id,name')
                ->orderByDesc('total_alloc')
                ->limit($this->limit)
                ->get();

            $labels  = $rows->map(fn ($r) => $r->employee?->name ?? '—')->toArray();
            $data    = $rows->pluck('total_alloc')->map(fn ($v) => (int) $v)->toArray();
            $dataset = "Allocation H.Supp — {$this->ref}";
        } else {
            // Grouper par type de jour (day_type)
            $rows = $query
                ->selectRaw('day_type, SUM(hours) as total_hours, SUM(alloc) as total_alloc')
                ->groupBy('day_type')
                ->orderByDesc('total_alloc')
                ->get();

            $labels  = $rows->map(fn ($r) => $r->day_type->label())->toArray();
            $data    = $rows->pluck('total_alloc')->map(fn ($v) => (int) $v)->toArray();
            $dataset = "Allocation par type — {$this->ref}";
        }

        return $this->buildDataset($labels, $data, $dataset, 'amber');
    }

    private function overtimesStats(): array
    {
        $q = Overtime::whereRef( $this->ref);
        return [
            ['label' => 'Total alloc.',  'value' => number_format($q->sum('alloc'), 0, ',', ' ') . ' F', 'icon' => '💸'],
            ['label' => 'Heures',        'value' => round($q->sum('hours'), 1) . ' h',                   'icon' => '⏱️'],
            ['label' => 'Employés',      'value' => $q->distinct('employee_id')->count('employee_id'),    'icon' => '👥'],
            ['label' => 'Entrées',       'value' => $q->count(),                                         'icon' => '📋'],
        ];
    }

    // ─── Builder dataset générique avec palette ───────────────────────

    private function buildDataset(array $labels, array $data, string $label, string $palette): array
    {
        $colors = [
            'indigo' => [[99,102,241],  [139,92,246]],  // indigo → violet
            'rose'   => [[244,63,94],   [251,113,133]], // rose → pink
            'amber'  => [[245,158,11],  [249,115,22]],  // amber → orange
        ][$palette] ?? [[99,102,241],[139,92,246]];

        $count = max(count($data), 1);
        $bgs   = array_map(function ($i) use ($colors, $count) {
            $t = $count > 1 ? $i / ($count - 1) : 0;
            $r = (int) round($colors[0][0] + $t * ($colors[1][0] - $colors[0][0]));
            $g = (int) round($colors[0][1] + $t * ($colors[1][1] - $colors[0][1]));
            $b = (int) round($colors[0][2] + $t * ($colors[1][2] - $colors[0][2]));
            return "rgba($r,$g,$b,0.85)";
        }, range(0, $count - 1));

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => $label,
                'data'            => $data,
                'backgroundColor' => $bgs,
                'borderRadius'    => 6,
                'borderSkipped'   => false,
            ]],
        ];
    }

    // ─── Réactivité ───────────────────────────────────────────────────

    public function updatedModule(): void
    {
        // Recalcule les refs disponibles pour le nouveau module
        $refs      = $this->availableRefs();
        $this->ref = in_array($this->ref, $refs) ? $this->ref : ($refs[0] ?? now()->format('m-Y'));
        $this->pushUpdate();
    }

    public function updatedRef(): void     { $this->pushUpdate(); }
    public function updatedGroupBy(): void { $this->pushUpdate(); }
    public function updatedLimit(): void   { $this->pushUpdate(); }

    private function pushUpdate(): void
    {
        $chartType = $this->groupBy === 'employee' ? 'bar' : 'doughnut';

        $this->dispatch('payroll-overview-updated',
            chartData : $this->chartData(),
            stats     : $this->stats(),
            availableRefs : $this->availableRefs(),
            chartType : $chartType,
        );
    }

    public function with()
    {
        return [
            'initialChartData' => $this->chartData(),
            'initialChartType' => $this->groupBy === 'employee' ? 'bar' : 'doughnut',
            'stats'            => $this->stats(),
            'availableRefs'    => $this->availableRefs(),
        ];
    }
}
?>

<div>

{{-- ── KPI Cards ── --}}
<div x-data="{ stats: {{ Js::from($stats) }} }" @payroll-overview-updated.window="stats = $event.detail.stats"
    class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <template x-for="kpi in stats" :key="kpi.label">
        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
            <div class="text-lg leading-none mb-1" x-text="kpi.icon"></div>
            <div class="text-[11px] text-gray-400 uppercase tracking-wide" x-text="kpi.label"></div>
            <div class="text-sm font-semibold text-gray-700 mt-0.5" x-text="kpi.value"></div>
        </div>
    </template>
</div>
<x-container>
    {{-- ── En-tête + contrôles ── --}}
    @dump($groupBy)
    <div class="flex  items-center justify-between">

        <div>
            <h2 class="text-base font-semibold text-gray-800">Analyse RH</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ match ($module) {
                    "leaves" => "Congés",
                    "overtimes" => "Heures supplémentaires",
                    default => "Rémunérations",
                } }}
                · {{ $ref }}
            </p>
        </div>

        <div class="flex items-center gap-2">

            {{-- Sélecteur de module --}}
            <flux:select wire:model.live="module">
                <option value="remunerations">💰 Rémunérations</option>
                <option value="leaves">🗓️ Congés</option>
                <option value="overtimes">⏱️ Heures supp.</option>
            </flux:select>

            {{-- Sélecteur de mois --}}
            <flux:select wire:model.live="ref">
                @foreach ($availableRefs as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </flux:select>

            {{-- Toggle groupBy --}}
            <flux:select wire:model.live="groupBy">
                <option value="type">Types</option>
                <option value="employee">Employes</option>
            </flux:select>

            {{-- Limite Top N --}}
            @if ($groupBy === "employee")
                <flux:select wire:model.live="limit">
                    @foreach ([5, 10, 20, 50] as $n)
                        <option value="{{ $n }}">Top {{ $n }}</option>
                    @endforeach
                </flux:select>
            @endif

        </div>
    </div>


    {{-- ── Graphique ── --}}
    <div wire:ignore x-data="payrollOverviewChart({{ Js::from($initialChartData) }}, '{{ $initialChartType }}')" x-init="init()"
        @payroll-overview-updated.window="onUpdate($event.detail)">
        <canvas x-ref="canvas" :class="chartType === 'doughnut' ? 'max-h-64' : 'max-h-72'"></canvas>
    </div>

</x-container>
</div>
