<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\IranEnum;

class CalculateIrans
{

    public function handle(Employee $employee,bool $inDatabase = false)
    {
        $employee->irans()?->delete();
        $irans = $employee->remunerations()
            ->whereIn('name', IranEnum::cases())
            ->get();
        $intermediateGrossTaxableSalary = $employee->salary?->intermediate_taxable_gross_salary;
        $totalIransAmount = 0;


        if ($irans && $inDatabase) {
            foreach ($irans as $iran) {
                $limit_fisc = $intermediateGrossTaxableSalary * $iran->name->taux();
                $employee->irans()->create([
                    'name' => $iran->name,
                    'amount' => $iran->amount,
                    'limit_fisc' => $limit_fisc,
                ]);
            }
            return;
        }
        if ($irans) {
            foreach ($irans as $iran) {
                $totalIransAmount += $iran->amount;
            }

            return $totalIransAmount;
        }
    }
}
