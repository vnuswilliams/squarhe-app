<?php

namespace App\Policies;

use App\Enums\CompanyRoleEnum;
use App\Enums\PermissionEnum;
use App\Models\PayrollBook;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollBookPolicy
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
    public function view(User $user, PayrollBook $payrollBook): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function download(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DOWNLOAD_PAYROLL->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can validated models.
     */
    public function validated(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VALIDATE_PAYROLL->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PayrollBook $payrollBook): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PayrollBook $payrollBook): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PayrollBook $payrollBook): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PayrollBook $payrollBook): bool
    {
        return false;
    }
}
