<?php

namespace App\Services;

use App\Enums\ContractTypeEnum;
use App\Models\Employee;
use Carbon\Carbon;

class CalculateUnemployementService
{
    private const TRANCHES = [
        [5, 0.2],
        [5, 0.25],
        [5, 0.30],
        [5, 0.35],
        [INF, 0.45],
    ];

    private function calculateSeniority(Employee $employee)
    {
        $startDate = Carbon::parse($employee->start_date);
        $endDate = $employee->end_date ? Carbon::parse($employee->end_date) : now();

        if (! $endDate->isLastOfMonth()) {
            $endDate = $endDate->subMonth()->endOfMonth();
        }

        return round($startDate->floatDiffInYears($endDate), 4);
    }

    private function isEligible(Employee $employee)
    {
        if ($employee->contract_type === ContractTypeEnum::CDD->value) {
            return false;
        }

        return $this->calculateSeniority($employee) >= 1;
    }


    public function handle(Employee $employee)
    {

        if (! $this->isEligible($employee))            return 0;

        $senerioty = $this->calculateSeniority($employee);
        $averageSalary = $$employee->salary->average_salary;
        $amount = 0;

        foreach (self::TRANCHES as [$yearss, $rate]) {
            if ($senerioty <= 0) {
                break;
            }
            $takenYears = min($senerioty, $yearss);

            $amount += ($averageSalary * $takenYears * $rate);

            $senerioty -= $takenYears;
        }

        return $amount;

    }
}
