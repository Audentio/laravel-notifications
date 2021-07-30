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
        list($contentType, $contentId) = $notification->getContentTypeId();
        $sender = $notification->getSender();
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'kind' => $notification->getKind(),
            'sender_user_id' => $sender ? $sender->getKey() : null,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'data' => $this->getData($user, $notification),
            'is_important' => $notification->isImportant(),
            'is_system' => $notification->isSystem(),
        ];
    }
}