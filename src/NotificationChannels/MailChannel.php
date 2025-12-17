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

        $result = parent::send($notifiable, $notification);

        if (extension_loaded('newrelic')) {
            $eventData = [
                'notification_type' => get_class($notification),
                'channel' => 'mail',
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->getKey(),
            ];

            if (isset($notifiable->realm) && $notifiable->realm) {
                $eventData['realm'] = $notifiable->realm->name;
            }

            newrelic_record_custom_event('NotificationSent', $eventData);
        }

        return $result;
    }
}
