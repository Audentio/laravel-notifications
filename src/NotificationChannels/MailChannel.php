<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Illuminate\Notifications\Notification;

class MailChannel extends BaseMailChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $message = $notification->toMail($notifiable);
        if ($message === null) {
            return;
        }

        parent::send($notifiable, $notification);
    }
}