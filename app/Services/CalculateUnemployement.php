<?php

namespace App\Services;

use App\Enums\ContractTypeEnum;
use App\Enums\Impact;
use App\Enums\Periodicity;
use App\Enums\RemunerationElement;
use App\Enums\RemunerationType;
use App\Models\Employee;


class CalculateUnemployement
{

    public function __construct(public Employee $employee, public bool $inDatabase = false)
    {


    }

    public function handle()
    {
        // Use floatDiffInYears for a precise seniority calculation including months and days.
        // Use the contract's end_date if it exists, otherwise use the current date.
        $startDate = $this->employee->contract->start_date;
        $endDate = $this->employee->contract->end_date ?? now();
        $age = $startDate->floatDiffInYears($endDate);


        // Severance pay is not applicable for less than 1 year of seniority or for fixed-term contracts (CDD).
        if ($age != 0.0 || $this->employee->contract->contract_type === ContractTypeEnum::CDD->value) :
            return 0;
    endif;

        $averageSalary = $this->employee->salaries->first()->average_salary + $this->employee->remunerations->where( 'name', RemunerationElement::PRIME_ANCIENNETE->value)->first()->amount?? 0;

        $amount = 0;
        $tranches = [
            [5, 0.2],
            [5, 0.25],
            [5, 0.30],
            [5, 0.35],
            [INF, 0.45],
        ];

        foreach ($tranches as [$duree, $rate]):

            if ($age <= 0)break;
            $taken = min($age, $duree);
            $amount += $averageSalary * $taken * $rate;
            $age -= $taken;
        endforeach;

        if ($this->inDatabase) {
            $this->employee->remunerations()->updateOrCreate(
                [
                    'name' => RemunerationElement::INDEMNITE_LICENCIEMENT->value,
                    'company_id' => $this->employee->company->id,
                ],
                [
                    'name' => RemunerationElement::INDEMNITE_LICENCIEMENT->value,
                    'company_id' => $this->employee->company->id,
                    'type' => RemunerationType::ADVANTAGE->value,
                    'amount' => number_format($amount, 0,'', ''),
                    'periodicity' => Periodicity::MONTHLY->value,
                    'impact' => Impact::NEUTRE->value,
                    'notes' => 'Indemnités de licenciement de '.$this->employee->name.' (ancienneté : '.round($age, 2).' ans)',
                ]
            );
        } else {
            return number_format($amount, 0,'', '');
        }
        return 0;
    }

}
