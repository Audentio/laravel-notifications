<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use Audentio\LaravelNotifications\Models\Interfaces\NotifiableUserInterface;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Illuminate\Notifications\Notification;

class MailChannel extends BaseMailChannel
{
    public function send($notifiable, Notification $notification)
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

        return parent::send($notifiable, $notification);
    }
}
