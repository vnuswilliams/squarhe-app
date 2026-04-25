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

    public function __construct(public Employee $employee, public bool $inDatabase = false) {}
    public function handle()
    {

        $leaves = $this->employee->leaves()
            ->where('type', LeaveTypeEnum::ANNUAL->value)
            ->whereMonth('start_date', Carbon::now()->month)
            ->first();

        if ($this->employee->start_date->age >= 1 && $leaves):

            $leaveBalance = $this->employee->leaves
                ->whereIn('type', [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])
                ->first();
            $lastLeave = $leaveBalance->last_leave ? Carbon::parse($leaveBalance->last_leave) : Carbon::parse($this->employee->start_date);
            $addon = $this->employee->remunerations->whereIn("name", [
                RemunerationEnum::PRIME_ANCIENNETE->value,
                RemunerationEnum::SUR_SALAIRE->value
            ])->sum('amount');


            $avgSalary = $this->employee->salary->average_salary + $addon ?? $this->employee->base_salary + $addon;


            $Pref = $lastLeave->diffInMonths(Carbon::parse($leaves->start_date));
            $employeeAge = Carbon::parse($this->employee->birth_date)->age;
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

            if ($this->employee->civility === CivilityEnum::FEMALE->value) {
                $DCS += $this->employee->child * $leaveBalance->leaves_child;
            }

            $DCS += intdiv($this->employee->start_date->age, 5) * $leaveBalance->leaves_seniority;

            $ACS = ($DCP > 0) ? ($ACP / $DCP) * $DCS : 0;
            $ACT = $ACP + $ACS;

            if ($this->inDatabase) {
                $this->employee->remunerations()->updateOrCreate(
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
