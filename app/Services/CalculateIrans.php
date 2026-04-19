<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\IranEnum;

class CalculateIrans
{

    public function __construct(public Employee $employee, public bool $inDatabase = false) {}
    public function handle()
    {
        $this->employee->irans()?->delete();
        $irans = $this->employee->remunerations()
            ->whereIn('name', IranEnum::cases())
            ->get();
        $intermediateGrossTaxableSalary = $this->employee->salaries?->intermediate_taxable_gross_salary;
        $totalIransAmount = 0;


        if ($irans && $this->inDatabase) {
            foreach ($irans as $iran) {
                $limit_fisc = $intermediateGrossTaxableSalary * $iran->name->taux();
                $this->employee->irans()->create([
                    'company_id' => $this->employee->company->id,
                    'name' => $iran->name,
                    'amount' => $iran->amount,
                    'limit_fisc' => $limit_fisc,
                    'quote' => min($iran->amount, $limit_fisc),
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
