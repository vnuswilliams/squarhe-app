<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeleteCompanyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Nombre de jours avant suppression définitive.
     */
    public const RETENTION_DAYS = 15;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Company $company,
        public readonly User $user,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deletionDate = now()->addDays(self::RETENTION_DAYS)->translatedFormat('d F Y');

        return (new MailMessage)
            ->subject(__('mail.delete_company.subject', [
                'company' => $this->company->name,
            ]))
            ->markdown('mail.company.delete-company', [
                'user'          => $this->user,
                'company'       => $this->company,
                'deletionDate'  => $deletionDate,
                'retentionDays' => self::RETENTION_DAYS,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'company_id'   => $this->company->id,
            'company_name' => $this->company->name,
            'deleted_at'   => now()->toISOString(),
            'purge_date'   => now()->addDays(self::RETENTION_DAYS)->toISOString(),
        ];
    }
}