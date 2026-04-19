<?php

namespace App\Policies;

use App\Enums\CompanyRoleEnum;
use App\Enums\PermissionEnum;
use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee $employee): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CRETATE_EMPLOYEE->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user,  Employee $employee): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_EMPLOYEE->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_EMPLOYEE->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee $employee): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }
}
