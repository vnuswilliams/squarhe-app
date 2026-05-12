<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\PayrollClosure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ClosePayrollJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PayrollClosure $closure,
        public bool $isImmediate = false,
        public int|string $company_id
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $closureId = $this->closure->id;
            $now = now();
            $ref = $this->closure->ref;
            $company = $this->closure->company;

            // Nettoyer les anciens snapshots si on relance une clôture non verrouillée
            $this->closure->salaries()->where('ref', $ref)->delete();
            $this->closure->employeeContributions()->where('ref', $ref)->delete();
            $this->closure->employerContributions()->where('ref', $ref)->delete();
            $this->closure->payslip()->where('ref', $ref)->delete();
            $this->closure->remunerations()->where('ref', $ref)->delete();
            $this->closure->irans()->where('ref', $ref)->delete();
            $this->closure->advnats()->where('ref', $ref)->delete();
            $this->closure->leaves()->where('ref', $ref)->delete();
            $this->closure->overtimes()->where('ref', $ref)->delete();
            $this->closure->declarations()->where('ref', $ref)->delete();
            $this->closure->payrollBook()->where('ref', $ref)->delete();

            // 2. Archivage au niveau des employés
            $employees = Employee::whereCompanyId($this->company_id)
                ->active()
                ->notInternship()
                ->withPayslip()
                ->with([
                    'payslip',
                    'salaries',
                    'remunerations',
                    'employeeContributions',
                    'leaves',
                    'irans',
                    'advnats',
                    'overtimes',
                    'employerContributions',
                ])->get();

            $payslipArr = [];
            $salariesArr = [];
            $remunerationsArr = [];
            $employeeContributionsArr = [];
            $leavesArr = [];
            $iransArr = [];
            $advnatsArr = [];
            $overtimesArr = [];
            $employerContributionsArr = [];
            $declarationsArr = [];
            $payrollBookArr = [];

            // 1. Archivage au niveau de l'entreprise (Livre de paie et Déclarations)
            if ($company->declarations) {
                $declarationsArr[] = $this->prepareSnapshot($company->declarations, $closureId, $now);
            }

            if ($company->payrollBook) {
                $payrollBookArr[] = $this->prepareSnapshot($company->payrollBook, $closureId, $now);
            }

            foreach ($employees as $employee) {
                // Bulletin de paie
                if ($employee->payslip) {
                    $payslipArr[] = $this->prepareSnapshot($employee->payslip, $closureId, $now);
                }

                // Salaire actuel
                if ($employee->salaries) {
                    $salariesArr[] = $this->prepareSnapshot($employee->salaries, $closureId, $now);
                }

                // Contributions (Salariales et Patronales)
                if ($employee->employeeContributions) {
                    $employeeContributionsArr[] = $this->prepareSnapshot($employee->employeeContributions, $closureId, $now);
                }
                if ($employee->employerContributions) {
                    $employerContributionsArr[] = $this->prepareSnapshot($employee->employerContributions, $closureId, $now);
                }

                // Éléments de rémunération (Primes, indemnités, etc.)
                foreach ($employee->remunerations as $remuneration) {
                    $remunerationsArr[] = $this->prepareSnapshot($remuneration, $closureId, $now);
                }
                // IRANS et Avantages en Nature (Snapshot de l'état actuel)
                foreach ($employee->irans as $iran) {
                    $iransArr[] = $this->prepareSnapshot($iran, $closureId, $now);
                }

                foreach ($employee->advnats as $advnat) {
                    $advnatsArr[] = $this->prepareSnapshot($advnat, $closureId, $now);
                }

                // Congés et Heures supplémentaires
                foreach ($employee->leaves as $leave) {
                    $leavesArr[] = $this->prepareSnapshot($leave, $closureId, $now);
                }

                foreach ($employee->overtimes as $overtime) {
                    $overtimesArr[] = $this->prepareSnapshot($overtime, $closureId, $now);
                }
            }

            if ($declarationsArr) {
                DB::table('declaration_snapshots')->insert($declarationsArr);
            }
            if ($payrollBookArr) {
                DB::table('payroll_book_snapshots')->insert($payrollBookArr);
            }
            if ($payslipArr) {
                DB::table('payslip_snapshots')->insert($payslipArr);
            }
            if ($salariesArr) {
                DB::table('salary_snapshots')->insert($salariesArr);
            }
            if ($remunerationsArr) {
                DB::table('remuneration_snapshots')->insert($remunerationsArr);
            }
            if ($employeeContributionsArr) {
                DB::table('employee_contribution_snapshots')->insert($employeeContributionsArr);
            }
            if ($leavesArr) {
                DB::table('leave_snapshots')->insert($leavesArr);
            }
            if ($iransArr) {
                DB::table('iran_snapshots')->insert($iransArr);
            }
            if ($advnatsArr) {
                DB::table('adv_nat_snapshots')->insert($advnatsArr);
            }
            if ($overtimesArr) {
                DB::table('overtime_snapshots')->insert($overtimesArr);
            }
            if ($employerContributionsArr) {
                DB::table('employer_contribution_snapshots')->insert($employerContributionsArr);
            }

            // 3. Verrouillage de la période

            // Finalisation de la clôture
            $this->closure->update([
                'status' => StatusEnum::CLOSED,
                'closed_at' => now(),
            ]);

            // Envoi des emails si l'option est activée
            if ($this->closure->send_payslips_by_email) {
                foreach ($company->employees as $employee) {
                    // SendPayslipEmailJob::dispatch($employee, $company, $ref);
                }
            }

            if ($this->isImmediate) {
                PrepareNextMonthPayrollJob::dispatch($company);
            }
        });
    }

    private function prepareSnapshot($model, int $closureId, $now)
    {
        return collect(Arr::except($model->toArray(), ['id', 'created_at', 'updated_at', 'deleted_at']))
            ->map(fn ($value) => is_array($value) ? json_encode($value) : $value)->toArray()
            +
            [
                'payroll_closure_id' => $closureId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
    }
}
