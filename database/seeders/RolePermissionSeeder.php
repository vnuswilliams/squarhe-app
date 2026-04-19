<?php

namespace Database\Seeders;

use App\Enums\CompanyRoleEnum;
use App\Enums\PermissionEnum;
use App\Jobs\SyncRolePermissionsJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions using the ownerPermission mapping from PermissionEnum
        foreach (PermissionEnum::cases() as $perm) {
            Permission::firstOrCreate(['name' => $perm->ownerPermission()]);
        }
        // Create roles from CompanyRoleEnum enum
        foreach (CompanyRoleEnum::cases() as $roleEnum) {
            Role::firstOrCreate(['name' => $roleEnum->value]);
        }

        // Dispatch jobs to sync role permissions (only if needed)
            SyncRolePermissionsJob::dispatch(CompanyRoleEnum::OWNER->value, 'owner');
            SyncRolePermissionsJob::dispatch(CompanyRoleEnum::ADMIN->value, 'admin');
            SyncRolePermissionsJob::dispatch(CompanyRoleEnum::MANAGER->value, 'manager');
    }
}
