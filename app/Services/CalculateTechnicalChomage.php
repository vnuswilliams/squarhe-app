<?php

namespace App\Services;

use App\Enums\ImpactEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\PeriodicityEnum;
use App\Models\Employee;

class CalculateTechnicalChomage
{
    public function handle(Employee $employee, int $month = 1, bool $inDatabase = false) 
    {
        $salaries = $employee->salary;
        $baseSalary = $salaries->base_salary ?? $employee->base_salary;
        $calculatePanc = app(CalculatePanc::class)->handle($employee);

        $panc = $calculatePanc;

        $baseOfCacul = $baseSalary + $panc;
        // cumulative rates: month 1 adds 50%, month 2 adds 40%, etc.
        // months > 6 add 20% each
        $rates = [
            1 => 0.50,
            2 => 0.40,
            3 => 0.35,
            4 => 0.30,
            5 => 0.25,
            6 => 0.20,
        ];

        $indemniteRate = 0.0;
        $months = max(1, (int) $month);
        for ($m = 1; $m <= $months; $m++) {
            $indemniteRate += $rates[$m] ?? 0.20;
        }

        $indemniteChomage = $baseOfCacul * $indemniteRate;

        if ($inDatabase) {
            $employee->remunerations()->updateOrCreate(
                [
                    'name' => RemunerationEnum::INDEMNITE_CHOMAGE_TECHNIQUE->value,
                ],
                [
                    'employee_id' => $employee->id,
                    'name' => RemunerationEnum::INDEMNITE_CHOMAGE_TECHNIQUE->value,
                    'type' => RemunerationTypeEnum::ALLOCATION->value,
                    'amount' => number_format($indemniteChomage, 0, '', ''),
                    'periodicity' => PeriodicityEnum::MONTHLY->value,
                    'impact' => ImpactEnum::TAXCOT->value,
                    'notes' => 'Indemnité de chômage technique'
                ]
            );
        } else {
            return number_format($indemniteChomage, 0, '', '');
        }
        return 0;
    }
}
