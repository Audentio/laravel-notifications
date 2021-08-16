<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use Audentio\LaravelNotifications\Models\Interfaces\NotifiableUserInterface;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Illuminate\Notifications\Notification;

class MailChannel extends BaseMailChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if ($notification instanceof AbstractNotification
            && $notifiable instanceof NotifiableUserInterface
            && !$notification->shouldBypassEmailVerificationCheck($notifiable)
            && !$notifiable->isEmailVerified()
        ) {
            return;
        }
        $message = $notification->toMail($notifiable);
        if ($message === null) {
            return;
        }

        parent::send($notifiable, $notification);
    }
}