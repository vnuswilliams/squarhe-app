<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationAcceptedRecipientNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invitation $invitation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Welcome to :company!', ['company' => $this->invitation->company->name]))
            ->greeting(__('Welcome, :name!', ['name' => $notifiable->name]))
            ->line(__('You have successfully joined :company as :role.', [
                'company' => $this->invitation->company->name,
                'role'    => $this->invitation->role,
            ]))
            ->action(__('Go to dashboard'), route('dashboard'));
    }
}