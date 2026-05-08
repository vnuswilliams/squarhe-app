<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\ImpactEnum;

class CalculatePanc
{


    public function handle(Employee $employee,  bool $inDatabase = false)
    {
        $age = $employee->start_date->age;
        $seniorityBonus = $employee->company->data['seniorityBonus'];

        $smic = $employee->salary->smic ?? $employee->base_salary;

        if ($age > 1 && $seniorityBonus['enabled']):

            $panc = $smic * ($age * $seniorityBonus['rate']);
            if ($inDatabase) {
                $employee->remunerations()->updateOrCreate(
                    [
                        'name' => RemunerationEnum::PRIME_ANCIENNETE->value,
                    ],
                    [
                        'employee_id' => $employee->id,
                        'name' => RemunerationEnum::PRIME_ANCIENNETE->value,
                        'type' => RemunerationTypeEnum::PRIME->value,
                        'amount' => $panc,
                        'periodicity' => PeriodicityEnum::UNIQUE->value,
                        'impact' => ImpactEnum::TAXCOT->value,
                    ]
                );
            } else {
                return $panc;
            }
        endif;
        return 0;
    }
}
