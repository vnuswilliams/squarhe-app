<?php

namespace App\Jobs;

use App\Enums\AvantageEnNatureType;
use App\Enums\Impact;
use App\Enums\IranEnum;
use App\Enums\LeavesType;
use App\Enums\RemunerationElement;
use App\Enums\RemunerationType;
use App\Enums\RetenuesEnums;
use App\Models\Employee;
use App\Services\CalculateAdvnats;
use App\Services\CalculateHsupp;
use App\Services\CalculateIrans;
use App\Services\CalculateLeave;
use App\Services\CalculatePanc;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class CalculateSalariesJob implements ShouldQueue
{
    use Dispatchable, Queueable;


    /**
     * Create a new job instance.
     */

    public function __construct(public Employee $employee, public array $exclude = [])
    {

        $this->exclude = [
            RemunerationElement::RETENUE_AVANCE_SALAIRE->value,
            RemunerationElement::RETENUE_PRET_EMPLOYE->value,
            RemunerationElement::RETENUE_SANCTION->value,
            RemunerationElement::SAISIE_SALAIRE->value,
            RemunerationElement::RETENUE_CANTINE->value,
            RemunerationElement::ACCOMPTE_SALAIRE->value,
            RemunerationElement::IRPP->value,
            RemunerationElement::CENTIME_COMMUNAL->value,
            RemunerationElement::FNE->value,
            RemunerationElement::CREDIT_FONCIER->value,
            RemunerationElement::TAXE_DEVELOPPEMENT->value,
            RemunerationElement::REDEVANCE_AUDIO_VISUELLE->value,
            RemunerationElement::SYNDICAT->value,
            RemunerationElement::CNPS_VIEILLESSE_SALARIALE->value,
            RemunerationElement::CNPS_VIEILLESSE_PATRONALE->value,
            RemunerationElement::CNPS_ALLOCATION_FAMILIALE->value,
            RemunerationElement::CNPS_ACCIDENT_MALADIE_PRO->value,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $employee = $this->employee;

        // vide d'abord la table et ensuite demarre les calculs
        $base_salary = $employee->contract->base_salary;
        // calcul du salaire moyen
        $salaries = $employee->salaries;

        $avgSalary = $salaries?->average_salary ?: $base_salary;
        $smic = $salaries?->smic ?: $base_salary;

        // Recuperation des conges payé annuel and hsupp and seiority bonus
        $calculatePanc = (new CalculatePanc($employee))->handle();
        $calculateLeave = (new CalculateLeave($employee))->handle();
        $caculateHsupp = (new CalculateHsupp($employee))->handle();

        // Calcul du salaire brut
        $grossSalary = $employee->remunerations()
            ->whereNotIn('name', $this->exclude)
            ->whereNotIn('type', [RemunerationType::IMPOT->value])
            ->sum('amount') + $base_salary + $calculatePanc + $calculateLeave + $caculateHsupp;

        // calcul du salaire brut taxable intermediaire
        $intermediateGrossTaxableSalary = $grossSalary - $employee->remunerations()
            ->where('impact', Impact::NEUTRE->value)
            ->whereIn('name', AvantageEnNatureType::cases())
            ->sum('amount');

        // calculate advnats and irans
        $calculateIrans = new CalculateIrans($employee, true);
        $calculateAdvnats = new CalculateAdvnats($employee, true);

        // Calcul du salaire taxable
        $taxableSalary = $intermediateGrossTaxableSalary + $employee->irans()
            ->sum('quote') +
            $employee->advnats()->sum('limit_fisc');

        // Caclcul du salaire cotisable
        $contributorSalary = $base_salary + $employee->remunerations()
            ->whereIn('impact', [Impact::COTISABLE->value, Impact::TAXCOT->value])
            ->sum('amount') + $employee->remunerations()
            ->whereIn('name', IranEnum::cases())->sum('amount') + $employee->advnats()->sum('excedent');

        // retenues aplicable
        $retenues = 0;
        $retenues = $employee->remunerations()
            ->whereIn('name', RetenuesEnums::cases())
            ->sum('amount');
        $daysLeft = $employee->leaves()
            ->whereIn('type', [LeavesType::SUSPENSION, LeavesType::INJUSTIFY_LEAVE])
            ->sum('days');
        $retenues += ($base_salary / $employee->company->companySetting->data['labourHours']) * $daysLeft;

        // net a payer salaire brut - (retenues + elements de contributions salarialles)
        $nap = $grossSalary - ($retenues + $employee->employeeContributions?->total);
        // ajout dans la table salaries (mise à jour des enregistrements liés)
        $employee->salaries()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'company_id' => $employee->company->id,
            ],
            [
                'employee_id' => $employee->id,
                'company_id' => $employee->company->id,
                'base_salary' => $base_salary,
                'gross_salary' => $grossSalary,
                'intermediate_taxable_gross_salary' => $intermediateGrossTaxableSalary,
                'taxable_gross_salary' => $taxableSalary,
                'contributory_salary' => $contributorSalary,
                'average_salary' => $avgSalary,
                'smic' => $smic,
                'retenues' => $retenues,
                'nap' => $nap
            ]
        );
    }
}
