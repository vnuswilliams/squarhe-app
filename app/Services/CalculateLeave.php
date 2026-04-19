<?php


namespace App\Services;
use App\Enums\Civility;
use App\Enums\LeavesType;
use App\Models\Employee;
use App\Enums\RemunerationType;
use App\Enums\RemunerationElement;
use App\Enums\Impact;
use App\Enums\Periodicity;
use Carbon\Carbon;


class CalculateLeave
{

    public function __construct(public Employee $employee, public bool $inDatabase = false)
    {
    }
    public function handle()
    {

        $leaves = $this->employee->leaves()
            ->where('type', LeavesType::ANNUAL->value)
            ->whereMonth('start_date', Carbon::now()->month)
            ->first();

        if ($this->employee->contract?->start_date->age >= 1 && $leaves):

            $leaveBalance = $this->employee->leaveBalance->first();
            $lastLeave = $leaveBalance->last_leave ? Carbon::parse($leaveBalance->last_leave) : Carbon::parse($this->employee->contract->start_date);
            $addon = $this->employee->remunerations->whereIn("name", [
                RemunerationElement::PRIME_ANCIENNETE->value,
                RemunerationElement::SUR_SALAIRE->value
            ])->sum('amount');

            
            $avgSalary = $this->employee->salaries->first()->average_salary + $addon ?? $this->employee->contract->base_salary + $addon;


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

            if ($this->employee->civility === Civility::FEMALE->value) {
                $DCS += $this->employee->child * $leaveBalance->leaves_child;
            }

            $DCS += intdiv($this->employee->contract->start_date->age, 5) * $leaveBalance->leaves_seniority;

            $ACS = ($DCP > 0) ? ($ACP / $DCP) * $DCS : 0;
            $ACT = $ACP + $ACS;

            if ($this->inDatabase) {
                $this->employee->remunerations()->updateOrCreate(
                    [
                        'name' => RemunerationElement::ALLOCATION_CONGE->value,
                    ],
                    [
                        'company_id' => $this->employee->company->id,
                        'name' => RemunerationElement::ALLOCATION_CONGE->value,
                        'type' => RemunerationType::ALLOCATION->value,
                        'amount' => number_format($ACT,0,'',''),
                        'periodicity' => Periodicity::MONTHLY->value,
                        'impact' => Impact::TAXCOT->value,
                        'notes' => 'Allocations congés annuel'
                    ]
                );
            } else {
                return number_format($ACT,0,'','');
            }

        endif;
        return 0;
    }

}