<?php

namespace App\Observers;

use App\Enums\LeaveTypeEnum;
use App\Models\Leave;
use App\Notifications\ActivityNotification;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class LeaveObserver
{
    /**
     * Handle the Leave "saving" event.
     */
    public function saving(Leave $leave): void
    {
        if ($leave->type === LeaveTypeEnum::SUSPENSION) {
            if ($leave->days > 8) {
                $leave->days = 8;
                $leave->end_date = $leave->start_date->copy()->addDays(8);
            }
        }

        if ($leave->type === LeaveTypeEnum::MATERNITY) {
            if ($leave->days < 98) {
                $leave->days = 98;
                $leave->end_date = $leave->start_date->copy()->addDays(98);
            }
        }
    }

    /**
     * Handle the Leave "created" event.
     */
    public function created(Leave $leave): void
    {
        $this->logActivity($leave, 'created', __('Congé ajouté à :name', ['name' => $leave->employee->name]));

        (new LeaveBalanceService())->updateLeaveBalance($leave);
    }

    /**
     * Handle the Leave "updated" event.
     */
    public function updated(Leave $leave): void
    {
        $this->logActivity($leave, 'updated', __('Congé modifié pour :name', ['name' => $leave->employee->name]));

        (new LeaveBalanceService())->updateLeaveBalance($leave);
    }

    /**
     * Handle the Leave "deleted" event.
     */
    public function deleted(Leave $leave): void
    {
        $this->logActivity($leave, 'deleted', __('Congé supprimé pour :name', ['name' => $leave->employee->name]));
    }

    /**
     * Log activity and notify company users.
     */
    private function logActivity(Leave $leave, string $event, string $message): void
    {
        $user = Auth::user();
        $employee = $leave->employee;

        if ($user && $employee && $employee->company) {
            Activity::causedBy($user)
                ->performedOn($leave)
                ->inLog('leave')
                ->event($event)
                ->withProperties([
                    'employee_name' => $employee->name,
                    'type' => $leave->type->value,
                ])
                ->log($message);

            $this->notifyCompanyUsers(
                $employee->company,
                $event,
                'Leave',
                $leave->type->value,
                $user->name ?? 'System',
                $employee->company->name
            );
        }
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
