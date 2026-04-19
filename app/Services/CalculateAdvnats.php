<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\AdvnatEnum;

class CalculateAdvnats
{
    public function __construct(public Employee $employee, public bool $inDatabase = false) {}
    public function handle()
    {
        $this->employee->advnats()->delete();
        $advnats = $this->employee->remunerations()
            ->whereIn('name', AdvnatEnum::cases())
            ->get();
        $intermediateGrossTaxableSalary = $this->employee->salaries?->intermediate_taxable_gross_salary;
        $totalAdvnatsAmount = 0;


        if ($advnats && $this->inDatabase) {
            foreach ($advnats as $advnat) {
                $limit_fisc = $intermediateGrossTaxableSalary * $advnat->name->taux();
                $this->employee->advnats()->create([
                    'company_id' => $this->employee->company->id,
                    'name' => $advnat->name,
                    'amount' => $advnat->amount,
                    'limit_fisc' => $limit_fisc,
                    'excedent' => max($advnat->amount, $limit_fisc) - min($advnat->amount, $limit_fisc),
                ]);
            }
            return;
        }

        if ($advnats) {
            foreach ($advnats as $advnat) {
                $totalAdvnatsAmount += $advnat->amount;
            }
            return $totalAdvnatsAmount;
        }
    }
}
