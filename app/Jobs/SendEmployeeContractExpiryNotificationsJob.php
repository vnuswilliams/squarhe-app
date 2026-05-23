<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeContractStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmployeeContractExpiryNotificationsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $today = now()->startOfDay();

        Employee::query()
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', $today->copy()->addDays(7))
            ->chunkById(100, function ($employees) use ($today): void {
                foreach ($employees as $employee) {
                    $endDate = $employee->end_date->copy()->startOfDay();

                    $status = $endDate->lt($today)
                        ? 'expired'
                        : ($endDate->equalTo($today) ? 'expires_today' : 'expires_soon');

                    $notification = new EmployeeContractStatusNotification($employee, $endDate, $status);

                    User::query()
                        ->where('company_id', $employee->company_id)
                        ->each(fn (User $user) => $user->notify($notification));
                }
            });
    }
}
