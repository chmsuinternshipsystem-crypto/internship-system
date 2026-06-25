<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InAppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $eventType,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $actionUrl = null,
        public readonly array $meta = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
            'meta' => $this->meta,
        ];
    }
}
