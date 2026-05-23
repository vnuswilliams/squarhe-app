<?php

namespace App\Notifications;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeContractStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Employee $employee,
        public Carbon $endDate,
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject())
            ->greeting(__('notif.contract_status_greeting'))
            ->line($this->message());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'employee_contract_status',
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'end_date' => $this->endDate->toDateString(),
            'status' => $this->status,
            'message' => $this->message(),
        ];
    }

    private function subject(): string
    {
        return match ($this->status) {
            'expired' => __('notif.contract_expired_subject', ['name' => $this->employee->name]),
            'expires_today' => __('notif.contract_expires_today_subject', ['name' => $this->employee->name]),
            default => __('notif.contract_expires_soon_subject', ['name' => $this->employee->name]),
        };
    }

    private function message(): string
    {
        return match ($this->status) {
            'expired' => __('notif.contract_expired_body', ['name' => $this->employee->name, 'date' => $this->endDate->format('Y-m-d')]),
            'expires_today' => __('notif.contract_expires_today_body', ['name' => $this->employee->name]),
            default => __('notif.contract_expires_soon_body', ['name' => $this->employee->name, 'days' => now()->startOfDay()->diffInDays($this->endDate->startOfDay())]),
        };
    }
}
