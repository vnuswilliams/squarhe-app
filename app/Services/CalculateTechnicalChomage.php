<?php

namespace App\Services;

use App\Enums\Impact;
use App\Enums\RemunerationElement;
use App\Enums\RemunerationType;
use App\Enums\Periodicity;
use App\Models\Employee;

class CalculateTechnicalChomage
{
    public function __construct(public Employee $employee, public int $month = 1, public bool $inDatabase = false)
    {
    }
    public function handle()    {
        $salaries = $this->employee->salaries->first();
        $baseSalary = $salaries->base_salary ?? $this->employee->contract->base_salary;
        $calculatePanc = new CalculatePanc($this->employee, $salaries->smic ?? $baseSalary);

        $panc = $this->employee->remunerations->where('name', RemunerationElement::PRIME_ANCIENNETE->value)->first();

        $baseOfCacul = $baseSalary + $panc->amount;
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
        $months = max(1, (int) $this->month);
        for ($m = 1; $m <= $months; $m++) {
            $indemniteRate += $rates[$m] ?? 0.20;
        }

        $indemniteChomage = $baseOfCacul * $indemniteRate;

        if ($this->inDatabase) {
            $this->employee->remunerations()->updateOrCreate(
                [
                    'name' => RemunerationElement::INDEMNITE_CHOMAGE_TECHNIQUE->value,
                    'company_id' => $this->employee->company->id,
                ],
                [
                    'employee_id' => $this->employee->id,
                    'company_id' => $this->employee->company->id,
                    'name' => RemunerationElement::INDEMNITE_CHOMAGE_TECHNIQUE->value,
                    'type' => RemunerationType::ALLOCATION->value,
                    'amount' => number_format($indemniteChomage, 0,'', ''),
                    'periodicity' => Periodicity::MONTHLY->value,
                    'impact' => Impact::TAXCOT->value,
                    'notes' => 'Indemnité de chômage technique'
                ]
            );
        } else {
            return number_format($indemniteChomage, 0,'', '');
        }
        return 0;
    }


}