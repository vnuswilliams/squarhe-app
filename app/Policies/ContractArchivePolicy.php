<?php

namespace App\Policies;

use App\Enums\CompanyRoleEnum;
use App\Enums\PermissionEnum;
use App\Models\ContractArchive;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContractArchivePolicy
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
    public function view(User $user, ContractArchive $contractArchive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_CONTRACT->ownerPermission()) || $user->hasRole(CompanyRoleEnum::OWNER);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContractArchive $contractArchive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContractArchive $contractArchive): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_CONTRACT->ownerPermission()) || $user->hasRole(CompanyRoleEnum::OWNER);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContractArchive $contractArchive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContractArchive $contractArchive): bool
    {
        return false;
    }
}
