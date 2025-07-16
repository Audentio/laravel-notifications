<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use Audentio\LaravelNotifications\Models\Interfaces\NotifiableUserInterface;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Illuminate\Notifications\Notification;

class MailChannel extends BaseMailChannel
{
    public function send($notifiable, Notification $notification): ?SentMessage
    {
        if ($notification instanceof AbstractNotification
            && $notifiable instanceof NotifiableUserInterface
            && !$notification->shouldBypassEmailVerificationCheck($notifiable)
            && !$notifiable->isEmailVerified()
        ) {
            return null;
        }

        $message = $notification->toMail($notifiable);
        if ($message === null) {
            return null;
        }

        parent::send($notifiable, $notification);
    }
}