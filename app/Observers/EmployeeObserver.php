<?php

namespace App\Observers;

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\NationalityEnum;
use App\Models\Employee;
use App\Notifications\ActivityNotification;
use App\Services\DeterminateLeaveEmployeeQuotaService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class EmployeeObserver
{
    public function changes(Employee $employee): void
    {
        $needsSave = false;
        $data = $employee->data ?? []; // Ensure $data is always an array

        // Rule 1: Civility Male -> child = 0
        if ($data['civility'] === CivilityEnum::MALE->value && $data['child'] !== 0) {
            $data['child'] = 0;
            $needsSave = true;
        }

        // Rule 2: Nationality Foreign -> contract_type CDD/ESSAY
        if ($data['nationality'] === NationalityEnum::FOREIGN->value && ! in_array($employee->contract_type, [ContractTypeEnum::CDD->value, ContractTypeEnum::ESSAY->value])) {
            $employee->contract_type = ContractTypeEnum::CDD->value;
            $needsSave = true;
        }

        // Rule 3: ContractType CDD & no end_date -> end_date = start_date + 2 years
        if ($employee->contract_type === ContractTypeEnum::CDD->value && ! $employee->end_date && $employee->start_date) {
            $employee->end_date = Carbon::parse($employee->start_date)->addYears(2);
            $needsSave = true;
        }

        if ($needsSave) {
            $employee->data = $data;
            $employee->saveQuietly();
        }
    }

    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        // Rule 4: Add 'syndicat' key with false value during creation
        $data = $employee->data ?? [];
        if (! isset($data['syndicat'])) {
            $data['syndicat'] = false;
            $employee->data = $data;
            $employee->saveQuietly();
        }
        app(DeterminateLeaveEmployeeQuotaService::class)->handle($employee);

        $employee->salary()->create([
            'base_salary' => $employee->base_salary,
        ]);

        $this->changes($employee);
        $user = Auth::user();
        $company = $employee->company;

        if ($user && $company) {
            // Log activity
            Activity::causedBy($user)
                ->performedOn($employee)
                ->inLog('employee')
                ->event('created')
                ->withProperties(['name' => $employee->name])
                ->log(__('Employé créé'));

            // Send notification to company users
            $this->notifyCompanyUsers(
                $company,
                'created',
                'Employee',
                $employee->name,
                $user->name,
                $company->name
            );
        }
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        $employee->salary()->update([
            'base_salary' => $employee->base_salary,
        ]);
        $this->changes($employee);

        $user = Auth::user();
        $company = $employee->company;

        if ($user && $company) {
            // Log activity
            Activity::causedBy($user)
                ->performedOn($employee)
                ->inLog('employee')
                ->event('updated')
                ->withProperties(['name' => $employee->name])
                ->log(__('Employé modifié'));

            // Send notification to company users
            $this->notifyCompanyUsers(
                $company,
                'updated',
                'Employee',
                $employee->name,
                $user->name,
                $company->name
            );
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        $user = Auth::user();
        $company = $employee->company;

        if ($user && $company) {
            // Log activity
            Activity::causedBy($user)
                ->performedOn($employee)
                ->inLog('employee')
                ->event('deleted')
                ->withProperties(['name' => $employee->name])
                ->log(__('Employé supprimé'));

            // Send notification to company users
            $this->notifyCompanyUsers(
                $company,
                'deleted',
                'Employee',
                $employee->name,
                $user->name,
                $company->name
            );
        }
        if($employee->contract_type != ContractTypeEnum::INTERNSHIP->value):
        app(SubscriptionService::class)->releaseEmployeeSlot($employee->company);
        endif;
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }

    /**
     * Notify all users from a company about an activity.
     */
    private function notifyCompanyUsers(
        $company,
        string $action,
        string $modelName,
        string $modelDisplayName,
        string $userName,
        string $companyName
    ): void {
        $companyUsers = $company->users()->get();

        foreach ($companyUsers as $notifiableUser) {
            $notifiableUser->notify(new ActivityNotification(
                action: $action,
                modelName: $modelName,
                modelDisplayName: $modelDisplayName,
                userName: $userName,
                companyName: $companyName
            ));
        }
    }
}
