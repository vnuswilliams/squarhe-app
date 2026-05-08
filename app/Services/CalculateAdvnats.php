<?php

namespace App\Services;

use App\Models\Employee;
use App\Enums\AdvnatEnum;

class CalculateAdvnats
{
    public function handle(Employee $employee,bool $inDatabase = false)
    {
        $employee->advnats()->delete();
        $advnats = $employee->remunerations()
            ->whereIn('name', AdvnatEnum::cases())
            ->get();
        $intermediateGrossTaxableSalary = $employee->salary?->intermediate_taxable_gross_salary;
        $totalAdvnatsAmount = 0;


        if ($advnats && $inDatabase) {
            foreach ($advnats as $advnat) {
                $limit_fisc = $intermediateGrossTaxableSalary * $advnat->name->taux();
                $employee->advnats()->create([
                    'name' => $advnat->name,
                    'amount' => $advnat->amount,
                    'limit_fisc' => $limit_fisc,
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
