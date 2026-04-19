<?php

namespace App\Jobs;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolePermissionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $roleName,
        private string $permissionType // 'owner', 'admin', 'manager'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $role = Role::findByName($this->roleName);

        if (!$role) {
            return;
        }

        // Build the expected permissions based on permission type
        $expectedPermissions = $this->getExpectedPermissions();

        // Get the role's current permissions
        $currentPermissions = $role->permissions->pluck('name')->toArray();

        // Check if all expected permissions are already assigned
        $missingPermissions = array_diff($expectedPermissions, $currentPermissions);

        // Only sync if there are missing permissions
        if (!empty($missingPermissions)) {
            $role->syncPermissions($expectedPermissions);
        }
    }

    /**
     * Get expected permissions based on permission type.
     */
    private function getExpectedPermissions(): array
    {
        $method = $this->permissionType . 'Permission';
        $permissions = [];

        foreach (PermissionEnum::cases() as $permEnum) {
            if (method_exists($permEnum, $method)) {
                $permissions[] = $permEnum->$method();
            }
        }

        return $permissions;
    }
}
