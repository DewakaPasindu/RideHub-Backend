<?php

namespace App\Notification\Services;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class NotificationService
{
    /**
     * Send a notification to a notifiable entity.
     */
    public function send(
        Notifiable $recipient,
        Notification $notification
    ): void {
        $recipient->notify($notification);
    }

    /**
     * Send a notification to multiple recipients.
     */
    public function sendMany(
        iterable $recipients,
        Notification $notification
    ): void {
        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }
    }
}