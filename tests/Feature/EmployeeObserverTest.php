<?php

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\NationalityEnum;
use App\Models\Company;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

// It's important to use RefreshDatabase to ensure a clean database state for each test.
// This trait will run migrations before each test.
uses(RefreshDatabase::class);

// Mocking Auth::user() might be necessary if the observer directly depends on it for company lookup.
// However, the 'changes' method is called before the Auth::user() check, so we can test it independently.
// We manually create a company to associate with the employee.

test('observer applies rules on employee creation', function () {
    // Create a company instance for association.
    $company = Company::create(['name' => 'Test Company', 'email' => 'test@company.com', 'phone' => '123456789', 'adresse' => '123 Test St', 'city' => 'Test City']);

    // Test Case 1: Civility Male, child set to 5 (should be overridden to 0).
    $employee1Data = [
        'company_id' => $company->id,
        'name' => 'John Doe',
        'civility' => CivilityEnum::MALE->value,
        'child' => 5, // User input, should be overridden
        'nationality' => NationalityEnum::CAMEROONIAN->value,
        'contract_type' => ContractTypeEnum::CDI->value,
        'start_date' => now()->toDateString(),
        'end_date' => null,
        'base_salary' => 50000,
        'department' => 'IT',
        'job_title' => 'Developer',
        'data' => [
            'child' => 5,
            'birth_date' => '1990-01-01',
            'phone' => '123456789',
            'niu' => 'NIU123',
            'cnps_number' => 'CNPS456',
            'email' => 'john.doe@example.com',
        ],
    ];
    $employee1 = Employee::create($employee1Data);
    $employee1Fresh = Employee::find($employee1->id); // Fetch again to ensure observer changes are loaded

    expect($employee1Fresh->data['child'])->toBe(0);
    expect($employee1Fresh->contract_type)->toBe(ContractTypeEnum::CDI->value); // Ensure other rules aren't triggered
    expect($employee1Fresh->end_date)->toBeNull();

    // Test Case 2: Nationality Foreign, contract_type CDI (should become CDD).
    $employee2Data = [
        'company_id' => $company->id,
        'name' => 'Jane Smith',
        'civility' => CivilityEnum::FEMALE->value,
        'child' => 1,
        'nationality' => NationalityEnum::FOREIGN->value,
        'contract_type' => ContractTypeEnum::CDI->value, // Should be changed to CDD
        'start_date' => now()->toDateString(),
        'end_date' => null,
        'base_salary' => 60000,
        'department' => 'HR',
        'job_title' => 'Recruiter',
        'data' => [
            'child' => 1,
            'birth_date' => '1985-05-15',
            'phone' => '987654321',
            'niu' => 'NIU789',
            'cnps_number' => 'CNPS012',
            'email' => 'jane.smith@example.com',
        ],
    ];
    $employee2 = Employee::create($employee2Data);
    $employee2Fresh = Employee::find($employee2->id);
    expect($employee2Fresh->nationality)->toBe(NationalityEnum::FOREIGN->value);
    expect($employee2Fresh->contract_type)->toBe(ContractTypeEnum::CDD->value); // Assert contract type is CDD

    // Test Case 3: ContractType CDD, no end_date (should calculate end_date).
    $startDate = now()->addMonth();
    $expectedEndDate = Carbon::parse($startDate)->addYears(2)->toDateString();

    $employee3Data = [
        'company_id' => $company->id,
        'name' => 'Peter Jones',
        'civility' => CivilityEnum::MALE->value,
        'child' => 0,
        'nationality' => NationalityEnum::CAMEROONIAN->value,
        'contract_type' => ContractTypeEnum::CDD->value,
        'start_date' => $startDate->toDateString(),
        'end_date' => null, // No end date provided
        'base_salary' => 55000,
        'department' => 'Finance',
        'job_title' => 'Accountant',
        'data' => [
            'child' => 0,
            'birth_date' => '1992-11-20',
            'phone' => '111222333',
            'niu' => 'NIU456',
            'cnps_number' => 'CNPS789',
            'email' => 'peter.jones@example.com',
        ],
    ];
    $employee3 = Employee::create($employee3Data);
    $employee3Fresh = Employee::find($employee3->id);
    expect($employee3Fresh->contract_type)->toBe(ContractTypeEnum::CDD->value);
    expect($employee3Fresh->end_date->toDateString())->toBe($expectedEndDate);

    // Test Case 4: Combined rules (Civility Male, Foreign nationality, CDI contract type)
    $startDateCombined = now()->addMonths(6);
    $expectedEndDateCombined = Carbon::parse($startDateCombined)->addYears(2)->toDateString();

    $employee4Data = [
        'company_id' => $company->id,
        'name' => 'Alice Wonderland',
        'civility' => CivilityEnum::MALE->value, // Rule 1: Should set child to 0
        'child' => 3, // User input, should be overridden
        'nationality' => NationalityEnum::FOREIGN->value, // Rule 2: Should set contract_type to CDD
        'contract_type' => ContractTypeEnum::CDI->value, // User input, will be changed by nationality rule
        'start_date' => $startDateCombined->toDateString(), // For Rule 3
        'end_date' => null, // For Rule 3
        'base_salary' => 70000,
        'department' => 'Marketing',
        'job_title' => 'Manager',
        'data' => [
            'child' => 3, // User input
            'birth_date' => '1988-07-01',
            'phone' => '444555666',
            'niu' => 'NIU101',
            'cnps_number' => 'CNPS121',
            'email' => 'alice.wonderland@example.com',
        ],
    ];
    $employee4 = Employee::create($employee4Data);
    $employee4Fresh = Employee::find($employee4->id);

    // Assertions for combined rules
    expect($employee4Fresh->civility)->toBe(CivilityEnum::MALE->value);
    expect($employee4Fresh->data['child'])->toBe(0); // Rule 1 applied

    expect($employee4Fresh->nationality)->toBe(NationalityEnum::FOREIGN->value);
    expect($employee4Fresh->contract_type)->toBe(ContractTypeEnum::CDD->value); // Rule 2 applied

    expect($employee4Fresh->contract_type)->toBe(ContractTypeEnum::CDD->value); // Rule 3 is for CDD, and contract_type is now CDD
    expect($employee4Fresh->end_date->toDateString())->toBe($expectedEndDateCombined); // Rule 3 applied
});
