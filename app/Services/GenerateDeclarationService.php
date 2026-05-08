<?php

namespace App\Services;

use App\Enums\PayslipItemsEnum;
use App\Enums\StatusEnum;
use App\Models\Declaration;
use App\Models\Employee;

class GenerateDeclarationService
{
    public function handle(int|string  $company_id): void
    {

        $employees = Employee::whereCompanyId($company_id)
        ->active()
        ->notInternship()
        ->withPayslip()
        ->get()
        ->sortBy('name');
        
        if ($employees->isEmpty()) {
            return;
        }
        $listEmployee = [];
        $empContribution = [];
        $emprContribution = [];
        $salaries = [];

        $all_emp_items = [
            PayslipItemsEnum::IRPP, PayslipItemsEnum::CENTIME_COMMUNAL, PayslipItemsEnum::TAXE_DEVELOPPEMENT,
            PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE, PayslipItemsEnum::CREDIT_FONCIER_SALARIALE,
            PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE,
        ];
        $all_empr_items = [
            PayslipItemsEnum::CREDIT_FONCIER_PATRONALE, PayslipItemsEnum::FNE,
            PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE, PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE,
            PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO,
        ];

        foreach ($employees as $emp) {
            $listEmployee[$emp->id] = $emp->short_name;
            $payslip = $emp->payslip; // Suppose que l'accesseur récupère le bulletin du mois courant

            if ($payslip) {
                foreach ($payslip['employee_contribution'] as $item) {
                    $empContribution[$item['code']][$emp->id] = $item['amount'] ?? 0;
                }

                foreach ($payslip['employer_contribution'] as $em) {
                    $emprContribution[$em['code']][$emp->id] = $em['amount'] ?? 0;
                }

                foreach ($payslip['salaries_data'] as $sal) {
                    foreach ($sal as $k => $pa) {
                        $salaries[$k][$emp->id] = $pa['amount'];
                    }
                }
            } else {
                $salaries['gross_salary'][$emp->id] = 0;
                $salaries['taxable_gross_salary'][$emp->id] = 0;
                $salaries['contributory_salary'][$emp->id] = 0;

                foreach ($all_emp_items as $item) {
                    $empContribution[$item->code()][$emp->id] = 0;
                }

                foreach ($all_empr_items as $item) {
                    $emprContribution[$item->code()][$emp->id] = 0;
                }
            }
        }

        Declaration::updateOrCreate(
            [
                'company_id' => $company_id
            ],
            [
                'status' => StatusEnum::APPROVED->value, // Ou un statut "Terminé" si disponible
                'data' => [
                    'listEmployee' => $listEmployee,
                    'empContribution' => $empContribution,
                    'emprContribution' => $emprContribution,
                    'salaries' => $salaries,
                ]
            ]
        );
    }
}
