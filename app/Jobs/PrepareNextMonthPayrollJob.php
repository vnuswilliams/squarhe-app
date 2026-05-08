<?php

namespace App\Jobs;

use App\Enums\PeriodicityEnum;
use App\Enums\StatusEnum;
use App\Models\Company;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PrepareNextMonthPayrollJob implements ShouldQueue
{
    use Queueable;
    
    public function __construct(public Company $company, public bool $isImmediate = false) {}

    public function handle(): void
    {

            $ref = now()->format('m-Y');
        $lockedPayroll = $this->company->payrollClosures()->where('ref', $ref)->where('status', StatusEnum::LOCKED)->first();
        if (!$lockedPayroll):
            /*          if ($this->isImmediate) {
                // Référence du mois N-1 (ex: "05-2024" si nous sommes en Juin)
                $lastMonthRef = now()->subMonth()->format('m-Y');

                // Vérification de l'existence d'une clôture validée pour le mois précédent
                $closure = $this->company->payrollClosures()
                    ->where('ref', $lastMonthRef)
                    ->whereIn('status', [PayrollClosureStatus::CLOSED, PayrollClosureStatus::DRAFT])
                    ->first();

                if (!$closure) {
                    // Récupérer les utilisateurs avec les rôles 'owner' ou 'admin' pour cette compagnie
                    $recipients = $this->company->users()
                        ->pluck('email')
                        ->toArray();

                    if (!empty($recipients)) {
                        Mail::to($recipients)->send(new MissingPayrollClosureMail($this->company, $lastMonthRef));
                    }
                }

                return;
            }
            */
   $this->company->payrollClosures()->update([
                'status' => StatusEnum::LOCKED,
                'closed_at' => now(),
            ]);

            // On parcourt les employés par lots pour la performance
            $employees =    $this->company
                ->activeEmployees()
                ->isNotInternship()
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
            foreach ($employees as $employee) {
                // Suppression des éléments variables du mois précédent
                // Exclus : documents, contract, leaveBalance (Leavesbalance)
                $employee->salaries()->where('ref', $ref)->delete();
                $employee->employeeContributions()->where('ref', $ref)->delete();
                $employee->employerContributions()->where('ref', $ref)->delete();
                $employee->payslip()->where('ref', $ref)->delete();
                $employee->overtimes()->where('ref', $ref)->delete();
                $employee->leaves()->where('ref', $ref)->delete();
                $employee->irans()->where('ref', $ref)->delete();
                $employee->advnats()->where('ref', $ref)->delete();

                // Pour les rémunérations : on supprime tout SAUF les mensuelles (périodicité fixe)
                $employee->remunerations()->where('ref', $ref)->where('periodicity', PeriodicityEnum::UNIQUE)->delete();

                $employee->remunerations()->update(['ref' => now()->addMonth()->format('m-Y')]);
            }
         
        endif;
    }
}