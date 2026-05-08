<?php

namespace App\Services;

use App\Enums\ContractTypeEnum;
use App\Enums\RemunerationEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\ImpactEnum;
use App\Models\Employee;
use App\Services\CalculatePanc;


class CalculateUnemployement
{


    public function handle(Employee $employee,  bool $inDatabase = false)
    {
        // Use floatDiffInYears for a precise seniority calculation including months and days.
        // Use the contract's end_date if it exists, otherwise use the current date.
        $startDate = $employee->start_date;
        $endDate = $employee->end_date ?? now();
        $age = $startDate->floatDiffInYears($endDate);


        // Severance pay is not applicable for less than 1 year of seniority or for fixed-term contracts (CDD).
        if ($age != 0.0 || $employee->contract_type === ContractTypeEnum::CDD->value) :
            return 0;
        endif;
        $calculatePanc = app(CalculatePanc::class)->handle($employee);

        $averageSalary = $employee->salary->average_salary +
            $calculatePanc;

        $amount = 0;
        $tranches = [
            [5, 0.2],
            [5, 0.25],
            [5, 0.30],
            [5, 0.35],
            [INF, 0.45],
        ];

        foreach ($tranches as [$duree, $rate]):

            if ($age <= 0) break;
            $taken = min($age, $duree);
            $amount += $averageSalary * $taken * $rate;
            $age -= $taken;
        endforeach;

        if ($inDatabase) {
            $employee->remunerations()->updateOrCreate(
                [
                    'name' => RemunerationEnum::INDEMNITE_LICENCIEMENT->value,
                ],
                [
                    'name' => RemunerationEnum::INDEMNITE_LICENCIEMENT->value,
                    'type' => RemunerationTypeEnum::ADVANTAGE->value,
                    'amount' => number_format($amount, 0, '', ''),
                    'periodicity' => PeriodicityEnum::MONTHLY->value,
                    'impact' => ImpactEnum::NEUTRE->value,
                    'notes' => 'Indemnités de licenciement de ' . $employee->name . ' (ancienneté : ' . round($age, 2) . ' ans)',
                ]
            );
        } else {
            return number_format($amount, 0, '', '');
        }
        return 0;
    }
}
