<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Leave;
use App\Enums\LeaveTypeEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Auth::login($this->user);

    $this->company = Company::create([
        'name' => 'Test Company',
        'email' => 'company@example.com',
        'phone' => '123456789',
        'adresse' => 'Test Address',
        'city' => 'Test City',
        'niu' => '123',
        'cnps' => '456',
        'rccm' => '789',
    ]);

    $this->employee = Employee::create([
        'company_id' => $this->company->id,
        'name' => 'John Doe',
        'start_date' => now(),
    ]);
});

it('caps suspension leave to 8 days', function () {
    $startDate = Carbon::parse('2026-05-01');
    $leave = Leave::create([
        'employee_id' => $this->employee->id,
        'type' => LeaveTypeEnum::SUSPENSION,
        'start_date' => $startDate,
        'days' => 10,
    ]);

    $leave->refresh();

    expect($leave->days)->toBe(8);
    expect($leave->end_date->toDateString())->toBe($startDate->copy()->addDays(8)->toDateString());
});

it('sets maternity leave to 98 days minimum', function () {
    $startDate = Carbon::parse('2026-05-01');
    $leave = Leave::create([
        'employee_id' => $this->employee->id,
        'type' => LeaveTypeEnum::MATERNITY,
        'start_date' => $startDate,
        'days' => 50,
    ]);

    $leave->refresh();

    expect($leave->days)->toBe(98);
    expect($leave->end_date->toDateString())->toBe($startDate->copy()->addDays(98)->toDateString());
});
