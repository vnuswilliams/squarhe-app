<?php

use App\Enums\CompanyRoleEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\PermissionEnum;
use App\Jobs\SyncRolePermissionsJob;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (PermissionEnum::cases() as $perm) {
        Permission::firstOrCreate(['name' => $perm->ownerPermission()]);
    }
    // Create roles from CompanyRoleEnum enum
    foreach (CompanyRoleEnum::cases() as $roleEnum) {
        Role::firstOrCreate(['name' => $roleEnum->value]);
    }

    // Dispatch jobs to sync role permissions (only if needed)
    Queue::fake();
    SyncRolePermissionsJob::dispatch(CompanyRoleEnum::OWNER->value, 'owner');
    SyncRolePermissionsJob::dispatch(CompanyRoleEnum::ADMIN->value, 'admin');
    SyncRolePermissionsJob::dispatch(CompanyRoleEnum::MANAGER->value, 'manager');
    Queue::assertPushed(SyncRolePermissionsJob::class);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Auth::login($this->user);
    $this->user->company()->create([
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
        'company_id' => $this->user->company_id,
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
