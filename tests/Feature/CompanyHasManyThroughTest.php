<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Remuneration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('retrieves payslips and remunerations directly from company through employees', function () {
    $company = Company::create([
        'name' => 'Acme Corp',
        'email' => 'acme@example.com',
        'phone' => '690000001',
        'adresse' => 'Rue 1',
        'city' => 'Douala',
    ]);

    $otherCompany = Company::create([
        'name' => 'Other Corp',
        'email' => 'other@example.com',
        'phone' => '690000002',
        'adresse' => 'Rue 2',
        'city' => 'Yaounde',
    ]);

    $employee = Employee::create([
        'company_id' => $company->id,
        'name' => 'Jean Dupont',
        'status' => 'Approved',
        'department' => 'software_engineering',
        'job_title' => 'Developer',
        'contract_type' => 'CDI',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'base_salary' => 100000,
    ]);

    $otherEmployee = Employee::create([
        'company_id' => $otherCompany->id,
        'name' => 'Outside Employee',
        'status' => 'Approved',
        'department' => 'software_engineering',
        'job_title' => 'Developer',
        'contract_type' => 'CDI',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'base_salary' => 100000,
    ]);

    $payslip = Payslip::create([
        'ref' => 'P-001',
        'employee_id' => $employee->id,
    ]);

    Payslip::create([
        'ref' => 'P-002',
        'employee_id' => $otherEmployee->id,
    ]);

    $remuneration = Remuneration::create([
        'ref' => 'R-001',
        'employee_id' => $employee->id,
        'name' => 'Prime',
        'type' => 'bonus',
        'amount' => '25000',
        'periodicity' => 'monthly',
        'impact' => 'gain',
    ]);

    Remuneration::create([
        'ref' => 'R-002',
        'employee_id' => $otherEmployee->id,
        'name' => 'Prime',
        'type' => 'bonus',
        'amount' => '25000',
        'periodicity' => 'monthly',
        'impact' => 'gain',
    ]);

    expect($company->payslips()->pluck('id')->all())->toBe([$payslip->id])
        ->and($company->remunerations()->pluck('id')->all())->toBe([$remuneration->id]);
});
