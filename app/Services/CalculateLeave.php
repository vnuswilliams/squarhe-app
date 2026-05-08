<?php


namespace App\Services;

use App\Enums\CivilityEnum;
use App\Enums\LeaveTypeEnum;
use App\Models\Employee;
use App\Enums\RemunerationEnum;
use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationTypeEnum;
use Carbon\Carbon;


class CalculateLeave
{

public function handle (Employee $employee, bool $inDatabase = false) 
       {

        $leaves = $employee->leaves()
            ->where('type', LeaveTypeEnum::ANNUAL->value)
            ->whereMonth('start_date', Carbon::now()->month)
            ->first();

        if ($employee->start_date->age >= 1 && $leaves):

            $leaveBalance = $employee->leaves
                ->whereIn('type', [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])
                ->first();
            $lastLeave = $leaveBalance->last_leave ? Carbon::parse($leaveBalance->last_leave) : Carbon::parse($employee->start_date);
            $addon = $employee->remunerations->whereIn("name", [
                RemunerationEnum::PRIME_ANCIENNETE->value,
                RemunerationEnum::SUR_SALAIRE->value
            ])->sum('amount');


            $avgSalary = $employee->salary->average_salary + $addon ?? $employee->base_salary + $addon;


            $Pref = $lastLeave->diffInMonths(Carbon::parse($leaves->start_date));
            $employeeAge = Carbon::parse($employee->birth_date)->age;
            $ACP = 0;

            if ($Pref === 12) {
                if ($employeeAge > 18) {
                    $ACP = $avgSalary / 16;
                } else {
                    $ACP = ($avgSalary * 5) / 48;
                }
            } elseif ($Pref > 12) {
                if ($employeeAge > 18) {
                    $ACP = ($avgSalary * $Pref) / 16;
                } else {
                    $ACP = ($avgSalary * $Pref * 5) / 48;
                }
            }

            $DCP = $Pref * $leaveBalance->leaves_majority;
            $DCS = 0;

            if ($employee->civility === CivilityEnum::FEMALE->value) {
                $DCS += $employee->child * $leaveBalance->leaves_child;
            }

            $DCS += intdiv($employee->start_date->age, 5) * $leaveBalance->leaves_seniority;

            $ACS = ($DCP > 0) ? ($ACP / $DCP) * $DCS : 0;
            $ACT = $ACP + $ACS;

            if ($inDatabase) {
                $employee->remunerations()->updateOrCreate(
                    [
                        'name' => RemunerationEnum::ALLOCATION_CONGE->value,
                    ],
                    [
                        'name' => RemunerationEnum::ALLOCATION_CONGE->value,
                        'type' => RemunerationTypeEnum::ALLOCATION->value,
                        'amount' => number_format($ACT, 0, '', ''),
                        'periodicity' => PeriodicityEnum::MONTHLY->value,
                        'impact' => ImpactEnum::TAXCOT->value,
                        'notes' => 'Allocations congés annuel'
                    ]
                );
            } else {
                return number_format($ACT, 0, '', '');
            }

        endif;
        return 0;
    }
}
