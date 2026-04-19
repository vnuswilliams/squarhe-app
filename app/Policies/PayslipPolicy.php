<?php

namespace App\Policies;

use App\Enums\CompanyRoleEnum;
use App\Enums\PermissionEnum;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayslipPolicy
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
    public function view(User $user, Payslip $payslip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payslip $payslip): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VALIDATED_PAYSLIP->ownerPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can validated the model.
     */
    public function validated(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VALIDATED_PAYSLIP->ownerPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can download the payslip.
     */
    public function download(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DOWNLOAD_PAYSLIP->ownerPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payslip $payslip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payslip $payslip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payslip $payslip): bool
    {
        return false;
    }
}
