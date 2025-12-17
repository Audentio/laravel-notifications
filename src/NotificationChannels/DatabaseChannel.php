<?php

namespace Audentio\LaravelNotifications\NotificationChannels;

use App\Models\User;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannel extends BaseDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        $result = parent::send($notifiable, $notification);

        if (extension_loaded('newrelic')) {
            $eventData = [
                'notification_type' => get_class($notification),
                'channel' => 'database',
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