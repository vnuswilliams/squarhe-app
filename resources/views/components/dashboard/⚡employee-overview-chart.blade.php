<?php
// app/Livewire/Dashboard/PayrollOverviewChart.php


use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Livewire\Component;

new class  extends Component
{
    public string $module = 'department'; // department | gender | age | contract | seniority | salary | nationality | status

    // ─── Config par module ────────────────────────────────────────────

    /** Retourne la config statique d'un module : type de chart, palette, etc. */
    public function moduleConfig(): array
    {
        return [
            'department'  => ['label' => '🏢 Département',       'chartType' => 'bar-h',   'palette' => 'indigo'],
            'gender'      => ['label' => '⚧ Répartition genre',  'chartType' => 'doughnut','palette' => 'rose'],
            'age'         => ['label' => '🎂 Pyramide des âges',  'chartType' => 'bar',     'palette' => 'violet'],
            'contract'    => ['label' => '📄 Type de contrat',    'chartType' => 'doughnut','palette' => 'amber'],
            'seniority'   => ['label' => '📅 Ancienneté',         'chartType' => 'bar',     'palette' => 'emerald'],
            'salary'      => ['label' => '💵 Tranches salariales','chartType' => 'bar',     'palette' => 'blue'],
            'nationality' => ['label' => '🌍 Nationalités',       'chartType' => 'bar-h',   'palette' => 'teal'],
            'status'      => ['label' => '✅ Statuts',            'chartType' => 'doughnut','palette' => 'zinc'],
        ];
    }

    // ─── Dispatcher central ───────────────────────────────────────────

    public function chartData(): array
    {
        return match ($this->module) {
            'department'  => $this->byDepartment(),
            'gender'      => $this->byGender(),
            'age'         => $this->byAge(),
            'contract'    => $this->byContract(),
            'seniority'   => $this->bySeniority(),
            'salary'      => $this->bySalary(),
            'nationality' => $this->byNationality(),
            'status'      => $this->byStatus(),
            default       => [],
        };
    }

    public function stats(): array
    {
        $base = Employee::query();

        return [
            ['label' => 'Effectif total', 'value' => Employee::count(),               'icon' => '👥'],
            ['label' => 'Actifs',         'value' => Employee::active()->count(),      'icon' => '✅'],
            ['label' => 'En attente',     'value' => Employee::pending()->count(),     'icon' => '⏳'],
            ['label' => 'Résiliés',       'value' => Employee::ofStatus(StatusEnum::TERMINATED)->count(), 'icon' => '🚫'],
        ];
    }

    // ─── Vues ─────────────────────────────────────────────────────────

    private function byDepartment(): array
    {
        $rows = Employee::selectRaw('department, COUNT(*) as total')
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();

        return $this->build(
            $rows->pluck('department')->toArray(),
            $rows->pluck('total')->toArray(),
            'Employés par département',
            'indigo'
        );
    }

    private function byGender(): array
    {
        // civility est dans data->civility (JSON)
        $rows = Employee::selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.civility')) as civility, COUNT(*) as total")
            ->groupBy('civility')
            ->get();

        $map    = ['M' => 'Homme', 'F' => 'Femme', '' => 'Non renseigné', null => 'Non renseigné'];
        $labels = $rows->map(fn ($r) => $map[$r->civility] ?? $r->civility ?? 'Non renseigné')->toArray();
        $data   = $rows->pluck('total')->toArray();

        return $this->build($labels, $data, 'Répartition par genre', 'rose');
    }

    private function byAge(): array
    {
        // Calcul de l'âge depuis data->birth_date (JSON)
        $employees = Employee::selectRaw(
            "TIMESTAMPDIFF(YEAR, JSON_UNQUOTE(JSON_EXTRACT(data, '$.birth_date')), CURDATE()) as age"
        )
        ->whereRaw("JSON_EXTRACT(data, '$.birth_date') IS NOT NULL")
        ->get();

        $tranches = [
            'Moins de 25 ans' => 0,
            '25 – 34 ans'     => 0,
            '35 – 44 ans'     => 0,
            '45 – 54 ans'     => 0,
            '55 ans et plus'  => 0,
        ];

        foreach ($employees as $e) {
            $age = (int) $e->age;
            match (true) {
                $age < 25             => $tranches['Moins de 25 ans']++,
                $age >= 25 && $age < 35 => $tranches['25 – 34 ans']++,
                $age >= 35 && $age < 45 => $tranches['35 – 44 ans']++,
                $age >= 45 && $age < 55 => $tranches['45 – 54 ans']++,
                default               => $tranches['55 ans et plus']++,
            };
        }

        return $this->build(
            array_keys($tranches),
            array_values($tranches),
            'Pyramide des âges',
            'violet'
        );
    }

    private function byContract(): array
    {
        $rows = Employee::selectRaw('contract_type, COUNT(*) as total')
            ->whereNotNull('contract_type')
            ->groupBy('contract_type')
            ->get();

        $labels = $rows->map(fn ($r) => $r->contract_type ? ContractTypeEnum::from($r->contract_type)->label() : '—')->toArray();
        $data   = $rows->pluck('total')->toArray();

        return $this->build($labels, $data, 'Types de contrat', 'amber');
    }

    private function bySeniority(): array
    {
        $employees = Employee::selectRaw(
            'TIMESTAMPDIFF(YEAR, start_date, CURDATE()) as years'
        )
        ->whereNotNull('start_date')
        ->get();

        $tranches = [
            'Moins d\'1 an' => 0,
            '1 – 2 ans'     => 0,
            '3 – 5 ans'     => 0,
            '6 – 10 ans'    => 0,
            'Plus de 10 ans'=> 0,
        ];

        foreach ($employees as $e) {
            $y = (int) $e->years;
            match (true) {
                $y < 1              => $tranches['Moins d\'1 an']++,
                $y >= 1 && $y < 3  => $tranches['1 – 2 ans']++,
                $y >= 3 && $y < 6  => $tranches['3 – 5 ans']++,
                $y >= 6 && $y < 11 => $tranches['6 – 10 ans']++,
                default            => $tranches['Plus de 10 ans']++,
            };
        }

        return $this->build(
            array_keys($tranches),
            array_values($tranches),
            'Ancienneté',
            'emerald'
        );
    }

    private function bySalary(): array
    {
        $employees = Employee::selectRaw('base_salary')
            ->whereNotNull('base_salary')
            ->where('base_salary', '>', 0)
            ->get();

        $tranches = [
            '< 100 000 F'          => 0,
            '100 – 250 000 F'      => 0,
            '250 – 500 000 F'      => 0,
            '500 000 – 1 000 000 F'=> 0,
            '> 1 000 000 F'        => 0,
        ];

        foreach ($employees as $e) {
            $s = (int) $e->base_salary;
            match (true) {
                $s < 100_000                          => $tranches['< 100 000 F']++,
                $s >= 100_000  && $s < 250_000        => $tranches['100 – 250 000 F']++,
                $s >= 250_000  && $s < 500_000        => $tranches['250 – 500 000 F']++,
                $s >= 500_000  && $s < 1_000_000      => $tranches['500 000 – 1 000 000 F']++,
                default                               => $tranches['> 1 000 000 F']++,
            };
        }

        return $this->build(
            array_keys($tranches),
            array_values($tranches),
            'Tranches salariales',
            'blue'
        );
    }

    private function byNationality(): array
    {
        $rows = Employee::selectRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(data, '$.nationality')) as nationality, COUNT(*) as total"
        )
        ->whereRaw("JSON_EXTRACT(data, '$.nationality') IS NOT NULL")
        ->groupBy('nationality')
        ->orderByDesc('total')
        ->limit(15)
        ->get();

        return $this->build(
            $rows->pluck('nationality')->toArray(),
            $rows->pluck('total')->toArray(),
            'Nationalités',
            'teal'
        );
    }

    private function byStatus(): array
    {
        $rows = Employee::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $labels = $rows->map(fn ($r) => StatusEnum::from($r->status)->label())->toArray();
        $data   = $rows->pluck('total')->toArray();

        return $this->build($labels, $data, 'Statuts', 'zinc');
    }

    // ─── Builder dataset ──────────────────────────────────────────────

    private function build(array $labels, array $data, string $label, string $palette): array
    {
        $palettes = [
            'indigo'  => [[99,102,241],  [139,92,246]],
            'rose'    => [[244,63,94],   [251,113,133]],
            'violet'  => [[139,92,246],  [168,85,247]],
            'amber'   => [[245,158,11],  [249,115,22]],
            'emerald' => [[16,185,129],  [20,184,166]],
            'blue'    => [[59,130,246],  [99,102,241]],
            'teal'    => [[20,184,166],  [16,185,129]],
            'zinc'    => [[113,113,122], [161,161,170]],
        ];

        $c     = $palettes[$palette] ?? $palettes['indigo'];
        $count = max(count($data), 1);

        $bgs = array_map(function ($i) use ($c, $count) {
            $t = $count > 1 ? $i / ($count - 1) : 0;
            return sprintf(
                'rgba(%d,%d,%d,0.85)',
                (int) round($c[0][0] + $t * ($c[1][0] - $c[0][0])),
                (int) round($c[0][1] + $t * ($c[1][1] - $c[0][1])),
                (int) round($c[0][2] + $t * ($c[1][2] - $c[0][2]))
            );
        }, range(0, $count - 1));

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => $label,
                'data'            => $data,
                'backgroundColor' => $bgs,
                'borderRadius'    => 6,
                'borderSkipped'   => false,
                'borderWidth'     => 0,
            ]],
        ];
    }

    // ─── Réactivité ───────────────────────────────────────────────────

    public function updatedModule(): void
    {
        $config = $this->moduleConfig()[$this->module] ?? [];

        $this->dispatch('demographics-updated',
            chartData  : $this->chartData(),
            stats      : $this->stats(),
            chartType  : $config['chartType'] ?? 'bar',
        );
    }

    public function with()
    {
        $config = $this->moduleConfig()[$this->module] ?? [];

        return  [
            'initialChartData' => $this->chartData(),
            'initialChartType' => $config['chartType'] ?? 'bar',
            'stats'            => $this->stats(),
            'modules'          => $this->moduleConfig(),
        ];
    }
}

?>
{{-- resources/views/livewire/dashboard/employee-demographics-chart.blade.php --}}

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">

    {{-- ── En-tête ── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Démographie RH</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $modules[$module]['label'] ?? '' }}
            </p>
        </div>

        {{-- Sélecteur de module --}}
        <select
            wire:model.live="module"
            class="text-sm border border-gray-200 rounded-xl px-3 py-2 text-gray-700
                   bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 shadow-sm"
        >
            @foreach($modules as $key => $cfg)
                <option value="{{ $key }}">{{ $cfg['label'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── KPI Cards ── --}}
    <div
        x-data="{ stats: {{ Js::from($stats) }} }"
        @demographics-updated.window="stats = $event.detail.stats"
        class="grid grid-cols-2 sm:grid-cols-4 gap-3"
    >
        <template x-for="kpi in stats" :key="kpi.label">
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                <div class="text-lg leading-none mb-1" x-text="kpi.icon"></div>
                <div class="text-[11px] text-gray-400 uppercase tracking-wide" x-text="kpi.label"></div>
                <div class="text-sm font-semibold text-gray-700 mt-0.5" x-text="kpi.value"></div>
            </div>
        </template>
    </div>

    {{-- ── Graphique ── --}}
    <div
        wire:ignore
        x-data="employeeDemographicsChart(
            {{ Js::from($initialChartData) }},
            '{{ $initialChartType }}'
        )"
        x-init="init()"
        @demographics-updated.window="onUpdate($event.detail)"
        class="relative"
    >
        {{-- Hauteur adaptée : doughnut moins haut que les barres --}}
        <canvas
            x-ref="canvas"
            :class="chartType === 'doughnut' ? 'max-h-64' : 'max-h-72'"
        ></canvas>
    </div>

</div>
