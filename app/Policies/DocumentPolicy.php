<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Document;
use App\Enums\CompanyRoleEnum;
use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;

class DocumentPolicy
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
    public function view(User $user, Document $document): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_DOCUMENT->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_DOCUMENT->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_DOCUMENT->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_DOCUMENT->adminPermission()) || $user->hasRole(CompanyRoleEnum::OWNER->value);;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}
