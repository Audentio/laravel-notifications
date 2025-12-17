<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use App\Models\User;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\PushNotification;

class PushNotificationChannel
{
    public function send(User $user, AbstractNotification $notification): void
    {
        $pushNotification = $notification->toPushNotification($user);
        if (!$pushNotification) {
            return;
        }

        /** @var UserPushSubscription[] $userPushSubscriptions */
        $userPushSubscriptions = $user->routeNotificationFor('push', $notification);
        if (empty($userPushSubscriptions)) {
            return;
        }

        foreach ($userPushSubscriptions as $userPushSubscription) {
            $pushNotification->queue($userPushSubscription);
        }

        if (extension_loaded('newrelic')) {
            $eventData = [
                'notification_type' => get_class($notification),
                'channel' => 'push',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->getKey(),
                'subscription_count' => count($userPushSubscriptions),
            ];

            if (isset($user->realm) && $user->realm) {
                $eventData['realm'] = $user->realm->name;
            }

            newrelic_record_custom_event('NotificationSent', $eventData);
        }
    }
}
