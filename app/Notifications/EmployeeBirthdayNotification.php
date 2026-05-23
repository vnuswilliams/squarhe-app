<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeBirthdayNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Employee $employee) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notif.employee_birthday_subject', ['name' => $this->employee->name]))
            ->greeting(__('notif.employee_birthday_greeting'))
            ->line(__('notif.employee_birthday_body', ['name' => $this->employee->name]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'employee_birthday',
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'message' => __('notif.employee_birthday_body', ['name' => $this->employee->name]),
        ];
    }
}
