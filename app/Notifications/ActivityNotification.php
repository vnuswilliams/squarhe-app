<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $action,
        public string $modelName,
        public string $modelDisplayName,
        public string $userName,
        public string $companyName,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'model_name' => $this->modelName,
            'model_display_name' => $this->modelDisplayName,
            'user_name' => $this->userName,
            'company_name' => $this->companyName,
        ];
    }
}
