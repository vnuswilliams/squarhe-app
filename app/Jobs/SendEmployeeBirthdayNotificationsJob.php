<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeBirthdayNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendEmployeeBirthdayNotificationsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $today = now()->format('m-d');

        Employee::query()
            ->whereNotNull('data->birth_day')
            ->whereRaw("DATE_FORMAT(JSON_UNQUOTE(JSON_EXTRACT(data, '$.birth_day')), '%m-%d') = ?", [$today])
            ->chunkById(100, function ($employees): void {
                foreach ($employees as $employee) {
                    $notification = new EmployeeBirthdayNotification($employee);

                    User::query()
                        ->where('company_id', $employee->company_id)
                        ->each(fn (User $user) => $user->notify($notification));

                    if (! empty($employee->data['email'] ?? null)) {
                        Notification::route('mail', $employee->data['email'])->notify($notification);
                    }
                }
            });
    }
}
