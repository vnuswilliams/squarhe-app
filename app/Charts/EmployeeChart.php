<?php

namespace App\Charts;

use App\Enums\CivilityEnum;
use App\Models\Company;
use vnusWilliams\LarapexCharts\LarapexChart;

class EmployeeChart
{

public function __construct(
    public ?Company $company
){}
    public function build(): \vnusWilliams\LarapexCharts\BarChart
    {
        return (new LarapexChart)->barChart()
            ->setTitle('San Francisco vs Boston.')
            ->setSubtitle('Wins during season 2021.')
            ->addData([6, 9, 3, 4, 10, 8], 'San Francisco')
            ->addData([7, 3, 8, 2, 6, 4], 'Boston')
            ->setXAxis(['January', 'February', 'March', 'April', 'May', 'June']);
    }

    public function agePyramids(): \vnusWilliams\LarapexCharts\BarChart|null
    {
        $employees = $this->company?->employees;
       if($employees){
        $dep =  [];
        foreach($employees as $emp)
        {
            $dep = array_unique([$emp->department]);
        }
        $female = $employees
            ->where('data.civility', CivilityEnum::FEMALE->value)
            ->groupBy('department')
            ->map(fn ($group) => $group->count())
            ->values()
            ->toArray();
        $mal = $employees->where('data.civility', CivilityEnum::MALE->value)
            ->map(fn ($group) => $group->count())
            ->values()
            ->toArray();
                return (new LarapexChart)->barChart()
                    ->setTitle('Femmes vs Hommes')
                    ->setSubtitle('Répartition des genres/département.')
                    ->addData($mal, 'Hommes')
                    ->addData($female, 'Femmes')
                    ->setXAxis($dep)
                    ->setStatesHover(LarapexChart::STATE_NONE)
                    ->setStatesActive(LarapexChart::STATE_DARKEN, true)
                        ->setGrid();

            }
            return null;
       }
}
