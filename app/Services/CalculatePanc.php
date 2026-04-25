<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\ImpactEnum;

class CalculatePanc
{


    public function __construct(public Employee $employee, public bool $inDatabase = false) {}
    public function handle()
    {
        $age = $this->employee->start_date->age;
        $seniorityBonus = $this->employee->company->data['seniorityBonus'];

        $smic = $this->employee->salary->smic ?? $this->employee->base_salary;

        if ($age > 1 && $seniorityBonus['enabled']):

            $panc = $smic * ($age * $seniorityBonus['rate']);
            if ($this->inDatabase) {
                $this->employee->remunerations()->updateOrCreate(
                    [
                        'name' => RemunerationEnum::PRIME_ANCIENNETE->value,
                    ],
                    [
                        'employee_id' => $this->employee->id,
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
