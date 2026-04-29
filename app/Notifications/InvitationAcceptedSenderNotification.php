<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationAcceptedSenderNotification extends Notification implements ShouldQueue
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
            ->subject(__(':name has joined :company!', [
                'name'    => $this->invitation->recipient->name,
                'company' => $this->invitation->company->name,
            ]))
            ->greeting(__('Great news, :name!', ['name' => $notifiable->name]))
            ->line(__(':user has accepted your invitation and joined :company with the role :role.', [
                'user'    => $this->invitation->recipient->name,
                'company' => $this->invitation->company->name,
                'role'    => $this->invitation->role,
            ]))
            ->action(__('View company'), route('dashboard'));
    }
}