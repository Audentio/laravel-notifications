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
    }
}