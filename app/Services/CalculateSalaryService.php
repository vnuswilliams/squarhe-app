<?php

namespace App\Services;

use App\Enums\ImpactEnum;
use App\Enums\IranEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\RetenuesEnum;
use App\Models\Employee;
use App\Services\CalculateAdvnats;
use App\Services\CalculateHsupp;
use App\Services\CalculateIransService;
use App\Services\CalculateLeave;
use App\Services\CalculatePanc;

class CalculateSalaryService
{
    public function handle( Employee $employee): void
    {
              // vide d'abord la table et ensuite demarre les calculs
        $base_salary = $employee->base_salary;
        // calcul du salaire moyen
        $salaries = $employee->salary;

        $avgSalary = $employee->data['average_salary'] ?: $base_salary;
        $smic = $employee->data['smic'] ?: $base_salary;

        // Recuperation des conges payé annuel and hsupp and seiority bonus
        $calculatePanc = app(CalculatePanc::class)->handle($employee);
        $calculateLeave =app (CalculateLeave::class)->handle($employee);
        $caculateHsupp = app(CalculateHsupp::class)->handle($employee);

        // Calcul du salaire brut
        $grossSalary = $employee->remunerations()
            ->whereNotIn('name', $this->exclude())
            ->whereNotIn('type', [RemunerationTypeEnum::IMPOT->value, RemunerationTypeEnum::RETENU->value])
            ->sum('amount') + $base_salary + $calculatePanc + $calculateLeave + $caculateHsupp;

        // calcul du salaire brut taxable intermediaire
        $intermediateGrossTaxableSalary = $grossSalary - $employee->remunerations()
            ->where('impact', ImpactEnum::NEUTRE->value)
            ->whereIn('name', IranEnum::cases())
            ->sum('amount');

        // calculate advnats and irans
        $calculateIrans = app(CalculateIransService::class)->handle($employee, true);
        $calculateAdvnats = app(CalculateAdvnats::class)->handle($employee, true);

        // Calcul du salaire taxable
        $taxableSalary = $intermediateGrossTaxableSalary + $employee->irans()
            ->sum('quote') +
            $employee->advnats()->sum('limit_fisc');

        // Caclcul du salaire cotisable
        $contributorSalary = $base_salary + $employee->remunerations()
            ->whereIn('impact', [ImpactEnum::COTISABLE->value, ImpactEnum::TAXCOT->value])
            ->sum('amount') + $employee->remunerations()
            ->whereIn('name', IranEnum::cases())->sum('amount') + $employee->advnats()->sum('excedent');

        // retenues aplicable
        $retenues = 0;
        $retenues = $employee->remunerations()
            ->whereIn('name', RetenuesEnum::cases())
                ->orWhere('type',  RemunerationTypeEnum::RETENU->value)
                ->sum('amount');
        $daysLeft = $employee->leaves()
            ->whereIn('type', [LeaveTypeEnum::SUSPENSION, LeaveTypeEnum::INJUSTIFY_LEAVE])
            ->sum('days');
        $retenues += ($base_salary / $employee->company->data['labourHours']) * $daysLeft;
       
        // ajout dans la table salaries (mise à jour des enregistrements liés)
        $employee->salary()->updateOrCreate(
            [
                'employee_id' => $employee->id,
            ],
            [
                'employee_id' => $employee->id,
                'base_salary' => $base_salary,
                'gross_salary' => $grossSalary,
                'intermediate_taxable_gross_salary' => $intermediateGrossTaxableSalary,
                'taxable_gross_salary' => $taxableSalary,
                'contributory_salary' => $contributorSalary,
                'average_salary' => $avgSalary,
                'smic' => $smic,
                'retenues' => $retenues,
            ]
        );
    }

    private function exclude(): array
    {

        return [
            RemunerationEnum::RETENUE_AVANCE_SALAIRE->value,
            RemunerationEnum::RETENUE_PRET_EMPLOYE->value,
            RemunerationEnum::RETENUE_SANCTION->value,
            RemunerationEnum::SAISIE_SALAIRE->value,
            RemunerationEnum::RETENUE_CANTINE->value,
            RemunerationEnum::ACCOMPTE_SALAIRE->value,
            RemunerationEnum::IRPP->value,
            RemunerationEnum::CENTIME_COMMUNAL->value,
            RemunerationEnum::FNE->value,
            RemunerationEnum::CREDIT_FONCIER->value,
            RemunerationEnum::TAXE_DEVELOPPEMENT->value,
            RemunerationEnum::REDEVANCE_AUDIO_VISUELLE->value,
            RemunerationEnum::SYNDICAT->value,
            RemunerationEnum::CNPS_VIEILLESSE_SALARIALE->value,
            RemunerationEnum::CNPS_VIEILLESSE_PATRONALE->value,
            RemunerationEnum::CNPS_ALLOCATION_FAMILIALE->value,
            RemunerationEnum::CNPS_ACCIDENT_MALADIE_PRO->value,
        ];
    }

}
