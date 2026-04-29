<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invitation $invitation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Dans la notification, le lien devient :
        $url = route('invitation.accept', [
            'company_code' => $this->invitation->company_code,
            'invitation' => $this->invitation->id,
        ]);

        return new MailMessage()
            ->subject(
                __('You have been invited to join :company', [
                    'company' => $this->invitation->company->name,
                ]),
            )
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(
                __(':sender has invited you to join :company as :role.', [
                    'sender' => $this->invitation->sender->name,
                    'company' => $this->invitation->company->name,
                    'role' => $this->invitation->role,
                ]),
            )
            ->action(__('Accept the invitation'), $url)
            ->line(__('This link expires in 48 hours.'))
            ->line(__('If you did not expect this invitation, you can ignore this email.'));
    }
}
