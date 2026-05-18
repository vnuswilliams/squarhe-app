<?php

namespace App\Services;

use App\Models\Employee;

class CalculateTechnicalUnemploymentService
{
    public function handle(Employee $employee, int $month = 1) 
    {
        $baseSalary = $employee->base_salary ;
        $panc = app(CalculatePanc::class)->handle($employee);        

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

        $indemniteTecUnemployment = 0;
        $months = max(1, (int) $month);
        
        for ($m = 1; $m <= $months; $m++) {
            $indemniteTecUnemployment += ($baseOfCacul * $rates[$m]);
        }

        return $indemniteTecUnemployment;

    }
}
