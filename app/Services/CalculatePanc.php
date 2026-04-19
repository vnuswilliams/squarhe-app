<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\RemunerationElement;
use App\Enums\RemunerationType;
use App\Enums\Periodicity;
use App\Enums\Impact;

class CalculatePanc
{


    public function __construct(public Employee $employee, public bool $inDatabase = false)
    {
    }
    public function handle()
    {
        $age = $this->employee->contract->start_date->age;
        $seniorityBonus = $this->employee->company->companySetting->data['seniorityBonus'];

        $smic = $this->employee->salaries->first()->smic ?? $this->employee->contract->base_salary;

        if ($age > 1 && $seniorityBonus['enabled']):

            $panc = $smic * ($age * $seniorityBonus['rate']);
            if ($this->inDatabase) {
                $this->employee->remunerations()->updateOrCreate(
                    [
                        'name' => RemunerationElement::PRIME_ANCIENNETE->value,
                        'company_id' => $this->employee->company->id,
                    ],
                    [
                        'employee_id' => $this->employee->id,
                        'company_id' => $this->employee->company->id,
                        'name' => RemunerationElement::PRIME_ANCIENNETE->value,
                        'type' => RemunerationType::PRIME->value,
                        'amount' => $panc,
                        'periodicity' => Periodicity::MONTHLY->value,
                        'impact' => Impact::TAXCOT->value,
                    ]
                );
            } else {
                return $panc;
            }
        endif;
        return 0;
    }

}