<?php

namespace App\Charts;

use App\Models\Company;
use vnusWilliams\LarapexCharts\LarapexChart;

class LeaveChart
{
       public function __construct(

public Company $company
    ){}
    public function leavePerDepertment(): \vnusWilliams\LarapexCharts\BarChart
    {
        $stats = $this->company->employees()
                ->join('leaves', 'employees.id', '=', 'leaves.employee_id')
                ->selectRaw('employees.department, SUM(leaves.days) as total_days')
                ->groupBy('employees.department')
                ->reorder()
                ->get();

        return (new LarapexChart)->barChart()
            ->setTitle('Absences ou congé par département')
            ->addData($stats->pluck('total_days')->toArray(), 'Total jour absence')
            ->setXAxis( array_unique($stats->pluck('department')->toArray()), "Département")
            ->setToolbar(show: true)
            ->setGrid();

    }
}
