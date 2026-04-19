<?php

namespace App\Jobs;

use App\Enums\AdvnatEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\PayslipItems;
use App\Enums\RemunerationElement;
use App\Enums\RetenuesEnums;
use App\Enums\StatusEnum;
use App\Jobs\CalculateSalariesJob;
use App\Models\Employee;
use App\Services\CalculateHsupp;
use App\Services\CalculateLeave;
use App\Services\CalculatePanc;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculatePayslipJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Employee $employee) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // $this->employee is already set from constructor
        // calculate salary here
        if ($this->employee->contract->contract_type != ContractTypeEnum::INTERNSHIP) {
            CalculateSalariesJob::dispatchSync($this->employee);


            $queryLeaves = $this->employee->leaves
                ->where('status', StatusEnum::APPROVED->value)
                ->sum('days');
            // Snapshot data
            $employeeData = [
                'name' => $this->employee->name,
                'job_title' => $this->employee->contract?->job_title,
                'start_date' => $this->employee->contract?->start_date?->format('d M Y'),
                'start_date_raw' => $this->employee->contract->anc, // for diffForHumans if needed or just calculation
                'cnps' => $this->employee->cnps,
                'professional_category' => $this->employee->contract?->professional_category,
                'leave_taken' => $this->employee->leaves
                    ->sum('days'),
                'overtimes_taken' => $this->employee->overtimes
                    ->sum('hours'),
                'day_worked' => (30 - $queryLeaves) < 0 ? 0 : 30 - $queryLeaves,
                'leaves_balance' => $this->employee->leaveBalance?->leaves_balance,
                'leaves_still' => $this->employee->leaveBalance?->leaves_balance,
                'sum_advnats' => $this->employee->remunerations
                    ->whereIn('name', AdvnatEnum::cases())
                    ->sum('amount'),
            ];

            $companyData = [
                'name' => $this->employee->company->name,
                'city' => $this->employee->company->city,
                'address' => $this->employee->company->adresse,
                'nui' => $this->employee->company->nui,
                'cnps' => $this->employee->company->cnps,
                'labour_hours' => $this->employee->company->companySetting->data['labourHours'],
                'paymentMethod' => $this->employee->company->companySetting->data['paymentMethod'],
                'applicable_law' => $this->employee->company->companySetting->data['applicable_law'],
            ];

            $elements = [];

            // Calculate Hsupp and put it in the payslip items
            $hsupp = (new CalculateHsupp($this->employee))->handle();
            if ($hsupp != 0) {
                $elements[] = [
                    'code' => PayslipItems::HEURE_SUPP->code(),
                    'label' => PayslipItems::HEURE_SUPP->label(),
                    'amount' => number_format($hsupp, 0, '', ''),
                ];
            }

            $leave = (new CalculateLeave($this->employee))->handle();
            if ($leave != 0) {
                $elements[] = [
                    'code' => PayslipItems::ALLOCATION_CONGE->code(),
                    'label' => PayslipItems::ALLOCATION_CONGE->label(),
                    'amount' => number_format($leave, 0, '', ''),
                ];
            }

            $panc = (new CalculatePanc($this->employee))->handle();
            if ($panc != 0) {
                $elements[] = [
                    'code' => PayslipItems::PRIME_ANCIENNETE->code(),
                    'label' => PayslipItems::PRIME_ANCIENNETE->label(),
                    'amount' => number_format($panc, 0, '', ''),
                ];
            }

            $elements[] = [
                'code' => PayslipItems::SALAIRE_BASE->code(),
                'label' => PayslipItems::SALAIRE_BASE->label(),
                'amount' => number_format($this->employee->contract->base_salary, 0, "", ""),
            ];

            // calculate payslip items
            $remuneration = $this->employee->remunerations()->sumByName()
                ->get();

            foreach ($remuneration as $rem) {
                $elements[] = [
                    'code' => $rem->name->code(),
                    'label' => $rem->name->label(),
                    'amount' => number_format($rem->total_amount, 0, '', ''),
                ];
            }
            usort($elements, function ($a, $b) {
                return intval($a['code']) <=> intval($b['code']);
            });


            // put the salaries in the payslipItems
            $salaries = $this->employee->salaries;


            $deduc = ($salaries->retenues ?? 0) + ($this->employee->employeeContributions->total ?? 0);


            $nap = $salaries->nap;

            $employerSalaries = [
                [PayslipItems::SALAIRE_BASE, $salaries->base_salary],
                [PayslipItems::GROSS_SALARY, $salaries->gross_salary],
                [PayslipItems::INTERMEDIATE_GROSS_SALARY, $salaries->intermediate_taxable_gross_salary],
                [PayslipItems::TAXABLE_GROSS_SALARY, $salaries->taxable_gross_salary],
                [PayslipItems::CONTRIBUTORY_SALARY, min($salaries->contributory_salary, 750000)],
                [PayslipItems::AVERAGE_SALARY, $salaries->average_salary],
                [PayslipItems::SMIC, $salaries->smic],
                [PayslipItems::NAD, $deduc],
                [PayslipItems::NAP, $nap],
            ];
            $salariesEmployee = [];
            foreach ($employerSalaries as [$payslipItemEnum, $amount]) {
                $salariesEmployee[] = [
                    $payslipItemEnum->value => [
                        'label' => $payslipItemEnum->label(),
                        'amount' => number_format($amount, 0, '', ''),
                    ],
                ];
            }

            // Calculate impots and put in the payslip items
            // calculate impot here
            calculateImpotForEmployee::dispatchSync($this->employee);

            $employeeCharge = $this->employee->employeeContributions()->first();
            $employeeContribution = [];
            if ($employeeCharge) {
                $employeePayslipItems = [
                    [PayslipItems::CNPS_VIEILLESSE_SALARIALE, $employeeCharge->old_age_pension],
                    [PayslipItems::IRPP, $employeeCharge->irpp],
                    [PayslipItems::CENTIME_COMMUNAL, $employeeCharge->cac],
                    [PayslipItems::CREDIT_FONCIER_SALARIALE, $employeeCharge->cfc],
                    [PayslipItems::SYNDICAT, $employeeCharge->syndicat],
                    [PayslipItems::REDEVANCE_AUDIO_VISUELLE, $employeeCharge->rav],
                    [PayslipItems::TAXE_DEVELOPPEMENT, $employeeCharge->tdl],
                ];

                foreach ($employeePayslipItems as [$payslipItemEnum, $amount]) {
                    $employeeContribution[] = [
                        'code' => $payslipItemEnum->code(),
                        'label' => $payslipItemEnum->label(),
                        'amount' => (int) number_format($amount, 0, '', ''),
                    ];
                }
                usort($employeeContribution, function ($a, $b) {
                    return intval($a['code']) <=> intval($b['code']);
                });
            }

            // put the employer impot in the payslipItems
            $employerCharge = $this->employee->employerContributions()->first();
            $employerContribution = [];
            if ($employerCharge) {
                $employerPayslipItems = [
                    [PayslipItems::CNPS_VIEILLESSE_PATRONALE, $employerCharge->old_age_pension],
                    [PayslipItems::CREDIT_FONCIER_PATRONALE, $employerCharge->cfc],
                    [PayslipItems::CNPS_ACCIDENT_MALADIE_PRO, $employerCharge->accident],
                    [PayslipItems::FNE, $employerCharge->fne],
                    [PayslipItems::CNPS_ALLOCATION_FAMILIALE, $employerCharge->family_allowance],
                ];

                foreach ($employerPayslipItems as [$payslipItemEnum, $amount]) {
                    $employerContribution[] = [
                        'code' => $payslipItemEnum->code(),
                        'label' => $payslipItemEnum->label(),
                        'amount' => (int) number_format($amount, 0, '', ''),
                    ];
                }
                usort($employerContribution, function ($a, $b) {
                    return intval($a['code']) <=> intval($b['code']);
                });
            }


 $injustifyLeaves = $this->employee->leaves
            ->whereIn('type', [LeaveTypeEnum::SUSPENSION, LeaveTypeEnum::INJUSTIFY_LEAVE])
            ->sum('days');
            $injustifyLeavesRetenues = (int) number_format($salaries->base_salary - (($salaries->base_salary / 30) * $injustifyLeaves), 0, '', '');


            $retenues[] = [
                'code' => RemunerationElement::RETENUE_ABSENCES->code(),
                'label' => RemunerationElement::RETENUE_ABSENCES->label(),
                'amount' => $injustifyLeavesRetenues,
            ];


            $employeeRetenues = $this->employee->remunerations()
                ->whereIn('name', RetenuesEnums::cases())
                ->sumByName()
                ->get();
            $retenues = [];
            foreach ($employeeRetenues as $ret) {
                $retenues[] = [
                    'code' => $ret->name->code(),
                    'label' => $ret->name->label(),
                    'amount' => (int) number_format($ret->total_amount, 0, '', ''),
                ];
            }

            usort($retenues, function ($a, $b) {
                return intval($a['code']) <=> intval($b['code']);
            });

            $this->employee->payslip()->updateOrCreate(
                [
                    'employee_id' => $this->employee->id,
                    'company_id' => $this->employee->company->id,
                ],
                [
                    'employee_id' => $this->employee->id,
                    'company_id' => $this->employee->company->id,
                    'employee_data' => $employeeData,
                    'company_data' => $companyData,
                    'employee_contribution' => $employeeContribution,
                    'employer_contribution' => $employerContribution,
                    'salaries_data' => $salariesEmployee,
                    'elements_data' => $elements,
                    'retenues_data' => $retenues,
                ]
            );
        }
    }
}
