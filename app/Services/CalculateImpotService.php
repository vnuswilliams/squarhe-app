<?php

namespace App\Services;

use App\Models\Employee;

class CalculateImpotService
{

public function handle(Employee $employee): void
    {
        $company = $employee->company;
        $salary = $employee->salary;

        // salaire cot plafonne
        $plafondCotisableSalary = min($salary->contributory_salary, 750000);
        // pension vieillesses
        $oldAgePension = $company->data['oldAgePension']['enabled'] ? $plafondCotisableSalary * $company->data['oldAgePension']['employeeShare'] : 0;

        // calcul cfc
        $cfc = $company->data['cfc']['enabled'] ? floor($salary->taxable_gross_salary / 1000) * 1000 * 0.01 : 0;

        // calcil syndicat
        $syndicat = $employee->data['syndicat'] ? ($salary->base_salary * 0.01) : 0;
        $tdl = 0;
        $rav = 0;
        $cac = 0;
        $irpp = 0;

        if ($company->data['rav']) {
            // calcul rav
            switch (true) {
                case $salary->taxable_gross_salary > 50000 && $salary->taxable_gross_salary <= 100000:
                    $rav = 750;
                    break;
                case $salary->taxable_gross_salary > 100000 && $salary->taxable_gross_salary <= 200000:
                    $rav = 1950;
                    break;
                case $salary->taxable_gross_salary > 200000 && $salary->taxable_gross_salary <= 300000:
                    $rav = 3250;
                    break;
                case $salary->taxable_gross_salary > 300000 && $salary->taxable_gross_salary <= 400000:
                    $rav = 4550;
                    break;
                case $salary->taxable_gross_salary > 400000 && $salary->taxable_gross_salary <= 500000:
                    $rav = 5850;
                    break;
                case $salary->taxable_gross_salary > 500000 && $salary->taxable_gross_salary <= 600000:
                    $rav = 7150;
                    break;
                case $salary->taxable_gross_salary > 600000 && $salary->taxable_gross_salary <= 700000:
                    $rav = 8450;
                    break;
                case $salary->taxable_gross_salary > 700000 && $salary->taxable_gross_salary <= 800000:
                    $rav = 9750;
                    break;
                case $salary->taxable_gross_salary > 800000 && $salary->taxable_gross_salary <= 900000:
                    $rav = 11050;
                    break;
                case $salary->taxable_gross_salary > 900000 && $salary->taxable_gross_salary <= 1000000:
                    $rav = 12350;
                    break;
                case $salary->taxable_gross_salary > 1000000:
                    $rav = 13000;
                    break;
            }
        }
        if ($company->data['tdl']) {
            switch (true) {
                case $salary->base_salary >= 62000 && $salary->base_salary <= 75000:
                    $tdl = 250;
                    break;
                case $salary->base_salary > 75000 && $salary->base_salary <= 100000:
                    $tdl = 500;
                    break;
                case $salary->base_salary > 100000 && $salary->base_salary <= 125000:
                    $tdl = 750;
                    break;
                case $salary->base_salary > 125000 && $salary->base_salary <= 150000:
                    $tdl = 1000;
                    break;
                case $salary->base_salary > 150000 && $salary->base_salary <= 200000:
                    $tdl = 1250;
                    break;
                case $salary->base_salary > 200000 && $salary->base_salary <= 250000:
                    $tdl = 1500;
                    break;
                case $salary->base_salary > 250000 && $salary->base_salary <= 300000:
                    $tdl = 2000;
                    break;
                case $salary->base_salary > 300000 && $salary->base_salary <= 500000:
                    $tdl = 2250;
                    break;
                case $salary->base_salary > 500000:
                    $tdl = 2500;
                    break;
            }
        }
        if ($company->data['irpp']) {
            // Calcul IRPP
            $snc = ($salary->taxable_gross_salary * 12) - ($salary->taxable_gross_salary * 0.3) - ($oldAgePension * 12);
            $ba = 500000;
            $irs = 0;

            switch (true) {
                case $snc <= 2000000:

                    $irs = (($snc - $ba) * 0.10);
                    break;

                case $snc > 2000000 && $snc <= 3000000:

                    $irs = ((($snc - $ba) - 2000000) * 0.15) + 200000;
                    break;

                case $snc > 3000000 && $snc <= 5000000:

                    $irs = ((($snc - $ba) - 3000000) * 0.25) + 350000;
                    break;
                case $snc > 5000000:

                    $irs = ((($snc - $ba) - 5000000) * 0.35) + 850000;
                    break;
            }

            $irpp = $irs > 0 ? $irs / 12 : 0;
            $cac = $irpp * 0.1;
        }

        $contri = $employee->employeeContributions()->updateOrCreate(
            [
                'employee_id' => $employee->id,
            ],
            [
                'employee_id' => $employee->id,
                'old_age_pension' => (int) number_format($oldAgePension, 0, '', ''),
                'irpp' => (int) number_format($irpp, 0, '', ''),
                'cac' => (int) number_format($cac, 0, '', '') ?? 0,
                'cfc' => (int) number_format($cfc, 0, '', '') ?? 0,
                'syndicat' => (int) number_format($syndicat, 0, '', ''),
                'rav' => (int) number_format($rav, 0, '', '') ?? 0,
                'tdl' => (int) number_format($tdl, 0, '', '') ?? 0,
            ]
        );

        $salary->update([
            'contributions' => $contri->total,
        ]);
        // allocation familiale

        $familyAllowance = $company->data['familyAllowances']['enabled'] ? $company->data['familyAllowances']['rate'] * $plafondCotisableSalary : 0;
        // accident maladie pro
        $accident = $company->data['accident']['enabled'] ? $salary->contributory_salary * $company->data['accident']['rate'] : 0;
        // fne
        $fne = $company->data['fne']['enabled'] ? $company->data['fne']['employerShare'] * $salary->taxable_gross_salary : 0;
        // cfc
        $cfc = $company->data['cfc']['enabled'] ? $company->data['cfc']['employerShare'] * $salary->taxable_gross_salary : 0;
        $employee->employerContributions()->updateOrCreate(
            [
                'employee_id' => $employee->id,
            ],
            [
                'employee_id' => $employee->id,
                'family_allowance' => (int) number_format($familyAllowance, 0, '', '') ?? 0,
                'old_age_pension' => (int) number_format($oldAgePension, 0, '', '') ?? 0,
                'accident' => (int) number_format($accident, 0, '', '') ?? 0,
                'cfc' => (int) number_format($cfc, 0, '', '') ?? 0,
                'fne' => (int) number_format($fne, 0, '', '') ?? 0,
            ]
        );
    }
}
