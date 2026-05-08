<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\PayrollBook;

class GeneratePayrollBookService
{
    
public function handle(int|string $company_id): void
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
    $array = [];
    $matrix = [];
    $employeeContribution = [];
    $employerContribution = [];


    $retenues = [];
    $salariesDetails = [];

    foreach ($employees as $emp) {
        $listEmployee[$emp->id] = $emp->short_name;
    }

    foreach ($employees as $emp) {
        if (!$emp->payslip || !isset($emp->payslip['elements_data'])) continue;
        foreach ($emp->payslip['elements_data'] as $pay) {
            $array[$pay['code']] = $pay['label'];
        }
    }

    foreach ($array as $code => $name) {
        $row = ['element' => $name, 'code' => $code];
        foreach ($listEmployee as $id => $empName) {
            $row[$id] = 0;
        }
        $matrix[$code] = $row;
    }

    foreach ($employees as $emp) {
        if (!$emp->payslip || !isset($emp->payslip['elements_data'])) continue;
        foreach ($emp->payslip['elements_data'] as $item) {
            if (isset($matrix[$item['code']])) {
                $matrix[$item['code']][$emp->id] = $item['amount'];
            }
        }
    }

    // Helper function to build matrices
    $buildMatrix = function ($sourceKey) use ($employees, $listEmployee) {
        $labels = [];
        $matrixData = [];
        foreach ($employees as $emp) {
            if (!$emp->payslip || !isset($emp->payslip[$sourceKey])) continue;
            foreach ($emp->payslip[$sourceKey] as $pay) {
                $labels[$pay['code']] = $pay['label'];
            }
        }
        foreach ($labels as $code => $name) {
            $row = ['element' => $name, 'code' => $code];
            foreach ($listEmployee as $id => $empName) {
                $row[$id] = 0;
            }
            $matrixData[$code] = $row;
        }
        foreach ($employees as $emp) {
            if (!$emp->payslip || !isset($emp->payslip[$sourceKey])) continue;
            foreach ($emp->payslip[$sourceKey] as $item) {
                 if (isset($matrixData[$item['code']])) {
                    $matrixData[$item['code']][$emp->id] = $item['amount'];
                }
            }
        }
        return $matrixData;
    };

    $employeeContribution = $buildMatrix('employee_contribution');
    $employerContribution = $buildMatrix('employer_contribution');
    $retenues = $buildMatrix('retenues_data');

    // salaries_data
    foreach ($employees as $emp) {
        if (!$emp->payslip || !isset($emp->payslip['salaries_data'])) continue;
        $payslips = $emp->payslip['salaries_data'];
        foreach ($payslips as $key => $pay) {
            foreach ($pay as $k => $pa) {
                $salariesDetails[$emp->id][$k] = $pa['amount'];
            }
        }
    }

    $payrollData = [
        'listEmployee' => $listEmployee,
        'matrix' => $matrix,
        'employeeContribution' => $employeeContribution,
        'employerContribution' => $employerContribution,
        'retenues' => $retenues,
        'salariesDetails' => $salariesDetails,
    ];

    // Store in DB
    PayrollBook::updateOrCreate(
        [
            'company_id' => $company_id,
        ],
        [
            'data' => $payrollData,
            'status' => StatusEnum::APPROVED->value,
        ]
    );
}
}
