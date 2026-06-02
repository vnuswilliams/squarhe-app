<?php

namespace App\Charts;

use App\Models\Company;
use vnusWilliams\LarapexCharts\LarapexChart;

class LeaveChart
{
    public function __construct(
        public ?Company $company
    ) {}
    public function leavePerEmployee(): ?\vnusWilliams\LarapexCharts\BarChart
    {
        if (!$this->company) {
            return null;
        }
        $stats = $this->company?->employees()
            ->join('leaves', 'employees.id', '=', 'leaves.employee_id')
            ->selectRaw('employees.name, SUM(leaves.days) as total_days')
            ->groupBy('employees.id', 'employees.name')
            ->reorder()
            ->get();
        return (new LarapexChart)->barChart()
            ->setTitle('Absences ou congé par collaborateur')
            ->addData($stats->pluck('total_days')->map(fn ($v): float => (float) $v)->toArray(), 'Total jour absence')
            ->setXAxis($stats->pluck('name')->toArray(), "Collaborateur")
            ->setToolbar(show: true)
            ->setGrid();
    }
    public function leavePerDepertment(): ?\vnusWilliams\LarapexCharts\BarChart
    {
        if (!$this->company) {
            return null;
        }

        $stats = $this->company?->employees()
        ->join('leaves', 'employees.id', '=', 'leaves.employee_id')
        ->selectRaw('employees.department, SUM(leaves.days) as total_days')
        ->groupBy('employees.department')
        ->reorder()
        ->get();

    return (new LarapexChart)->barChart()
        ->setTitle('Absences ou congé par département')
        ->addData(
            $stats->pluck('total_days')->toArray(),
            'Total jour absence'
        )
        ->setXAxis(
            $stats->pluck('department')
                ->map(fn ($department) => $department->value)
                ->unique()
                ->values()
                ->toArray(),
            'Département'
        )
        ->setToolbar(show: true)
        ->setGrid();
    }

    public function leavePerType(): ?\vnusWilliams\LarapexCharts\PieChart
    {
        if (!$this->company) {
            return null;
        }

        $stats = $this->company?->employees()
            ->join('leaves', 'employees.id', '=', 'leaves.employee_id')
            ->selectRaw('leaves.type, SUM(leaves.days) as total_days')
            ->groupBy('leaves.type')
            ->reorder()
            ->get();

        $labels = $stats->map(function ($stat): string {
            $typeVal = $stat->type instanceof \App\Enums\LeaveTypeEnum ? $stat->type->value : (string) $stat->type;
            $enum = \App\Enums\LeaveTypeEnum::tryFrom($typeVal);
            return $enum ? $enum->label() : $typeVal;
        })->toArray();

        return (new LarapexChart)->pieChart()
            ->setTitle('Absences ou congé par type')
            ->addData($stats->pluck('total_days')->map(fn ($v): float => (float) $v)->toArray())
            ->setLabels($labels);
    }

    public function leavePerStatus(): ?\vnusWilliams\LarapexCharts\DonutChart
    {
        if (!$this->company) {
            return null;
        }
        $stats = $this->company?->employees()
            ->join('leaves', 'employees.id', '=', 'leaves.employee_id')
            ->selectRaw('leaves.status, SUM(leaves.days) as total_days')
            ->groupBy('leaves.status')
            ->reorder()
            ->get();

        $labels = $stats->map(function ($stat): string {
            $statusVal = $stat->status instanceof \App\Enums\StatusEnum ? $stat->status->value : (string) $stat->status;
            $enum = \App\Enums\StatusEnum::tryFrom($statusVal);
            return $enum ? $enum->label() : $statusVal;
        })->toArray();

        return (new LarapexChart)->donutChart()
            ->setTitle('Absences ou congé par statut')
            ->addData($stats->pluck('total_days')->map(fn ($v): float => (float) $v)->toArray())
            ->setLabels($labels);
    }
}
