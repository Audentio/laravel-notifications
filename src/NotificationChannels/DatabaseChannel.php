<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use App\Models\User;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannel extends BaseDatabaseChannel
{
    protected function buildPayload($notifiable, Notification $notification)
    {
        /** @var User $notifiable */
        /** @var AbstractNotification $notification */

        if (!$notification instanceof AbstractNotification) {
            throw new \RuntimeException('Notification ' . get_class($notification) . ' does not extend ' .
                'AbstractNotification');
        }

        return $this->buildNotificationPayload($notifiable, $notification);
    }

    protected function buildNotificationPayload(User $user, AbstractNotification $notification): array
    {
        $contentTypeId = $notification->getContentTypeId();
        $sender = $notification->getSender();
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'kind' => $notification->getKind(),
            'sender_user_id' => $sender ? $sender->getKey() : null,
            'content_type' => $contentTypeId['content_type'],
            'content_id' => $contentTypeId['content_id'],
            'data' => $this->getData($user, $notification),
            'is_important' => $notification->isImportant(),
            'is_system' => $notification->isSystem(),
        ];
    }
}