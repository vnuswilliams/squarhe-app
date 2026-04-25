<?php

namespace App\Jobs;

use App\Enums\AdvnatEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\PayslipItemsEnum;
use App\Enums\RetenuesEnum;
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
        if ($this->employee->contract_type != ContractTypeEnum::INTERNSHIP) {
            CalculateSalariesJob::dispatchSync($this->employee);


            $queryLeaves = $this->employee->leaves
                ->where('status', StatusEnum::APPROVED->value)
                ->sum('days');
                $leaveBalance =  $this->employee->leaves
                 ->whereIn('type', [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])
                 ->value('leave_balance');
            // Snapshot data
            $employeeData = [
                'name' => $this->employee->name,
                'job_title' => $this->employee->job_title,
                'start_date' => $this->employee->start_date?->format('d M Y'),
                'start_date_raw' => $this->employee->anc, // for diffForHumans if needed or just calculation
                'cnps' => $this->employee->cnps,
                'professional_category' => $this->employee->data['category'],
                'leave_taken' => $this->employee->leaves
                    ->sum('days'),
                'overtimes_taken' => $this->employee->overtimes
                    ->sum('hours'),
                'day_worked' => (30 - $queryLeaves) < 0 ? 0 : 30 - $queryLeaves,
                'leaves_balance' => $leaveBalance?->leaves_balance,
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
                'labour_hours' => $this->employee->company->data['labourHours'],
                'paymentMethod' => $this->employee->company->data['paymentMethod'],
                'applicable_law' => $this->employee->company->data['applicable_law'],
            ];

            $elements = [];

            // Calculate Hsupp and put it in the payslip items
            $hsupp = (new CalculateHsupp($this->employee))->handle();
            if ($hsupp != 0) {
                $elements[] = [
                    'code' => PayslipItemsEnum::HEURE_SUPP->code(),
                    'label' => PayslipItemsEnum::HEURE_SUPP->label(),
                    'amount' => number_format($hsupp, 0, '', ''),
                ];
            }

            $leave = (new CalculateLeave($this->employee))->handle();
            if ($leave != 0) {
                $elements[] = [
                    'code' => PayslipItemsEnum::ALLOCATION_CONGE->code(),
                    'label' => PayslipItemsEnum::ALLOCATION_CONGE->label(),
                    'amount' => number_format($leave, 0, '', ''),
                ];
            }

            $panc = (new CalculatePanc($this->employee))->handle();
            if ($panc != 0) {
                $elements[] = [
                    'code' => PayslipItemsEnum::PRIME_ANCIENNETE->code(),
                    'label' => PayslipItemsEnum::PRIME_ANCIENNETE->label(),
                    'amount' => number_format($panc, 0, '', ''),
                ];
            }

            $elements[] = [
                'code' => PayslipItemsEnum::SALAIRE_BASE->code(),
                'label' => PayslipItemsEnum::SALAIRE_BASE->label(),
                'amount' => number_format($this->employee->base_salary, 0, "", ""),
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


            // put the salaries in the payslipItemsEnum
            $salaries = $this->employee->salary;


            $deduc = ($salaries->retenues ?? 0) + ($this->employee->employeeContributions->total ?? 0);


            $nap = $salaries->nap;

            $employerSalaries = [
                [PayslipItemsEnum::SALAIRE_BASE, $salaries->base_salary],
                [PayslipItemsEnum::GROSS_SALARY, $salaries->gross_salary],
                [PayslipItemsEnum::INTERMEDIATE_GROSS_SALARY, $salaries->intermediate_taxable_gross_salary],
                [PayslipItemsEnum::TAXABLE_GROSS_SALARY, $salaries->taxable_gross_salary],
                [PayslipItemsEnum::CONTRIBUTORY_SALARY, min($salaries->contributory_salary, 750000)],
                [PayslipItemsEnum::AVERAGE_SALARY, $salaries->average_salary],
                [PayslipItemsEnum::SMIC, $salaries->smic],
                [PayslipItemsEnum::NAD, $deduc],
                [PayslipItemsEnum::NAP, $nap],
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
                    [PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE, $employeeCharge->old_age_pension],
                    [PayslipItemsEnum::IRPP, $employeeCharge->irpp],
                    [PayslipItemsEnum::CENTIME_COMMUNAL, $employeeCharge->cac],
                    [PayslipItemsEnum::CREDIT_FONCIER_SALARIALE, $employeeCharge->cfc],
                    [PayslipItemsEnum::SYNDICAT, $employeeCharge->syndicat],
                    [PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE, $employeeCharge->rav],
                    [PayslipItemsEnum::TAXE_DEVELOPPEMENT, $employeeCharge->tdl],
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

            // put the employer impot in the payslipItemsEnum
            $employerCharge = $this->employee->employerContributions()->first();
            $employerContribution = [];
            if ($employerCharge) {
                $employerPayslipItems = [
                    [PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE, $employerCharge->old_age_pension],
                    [PayslipItemsEnum::CREDIT_FONCIER_PATRONALE, $employerCharge->cfc],
                    [PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO, $employerCharge->accident],
                    [PayslipItemsEnum::FNE, $employerCharge->fne],
                    [PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE, $employerCharge->family_allowance],
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
                'code' => PayslipItemsEnum::RETENUE_ABSENCES->code(),
                'label' => PayslipItemsEnum::RETENUE_ABSENCES->label(),
                'amount' => $injustifyLeavesRetenues,
            ];


            $employeeRetenues = $this->employee->remunerations()
                ->whereIn('name', RetenuesEnum::cases())
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
