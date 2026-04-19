<?php

namespace App\Observers;

use App\Enums\CompanyRoleEnum;
//use App\Jobs\SyncRolePermissionsJob;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyChangedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        $user = Auth::user();

        if ($user) {
            $user->update([
                'company_id' => $company->id,
            ]);

            $user->assignRole(CompanyRoleEnum::OWNER->value);
            $user->notify(new CompanyChangedNotification($company, 'created'));
        }
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        Notification::send($company->users()->get(), new CompanyChangedNotification($company, 'updated'));
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        $user = Auth::user();

        if ($user && $user->company_id === $company->id) {
            $user->update([
                'company_id' => null,
            ]);
        }

        Notification::send($company->users()->get(), new CompanyChangedNotification($company, 'deleted'));
    }

    /**
     * Handle the Company "restored" event.
     */
    public function restored(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        //
    }
}
