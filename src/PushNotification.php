<?php

namespace Audentio\LaravelNotifications;

use App\Models\User;
use App\Models\UserPushQueue;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Carbon\Carbon;

class PushNotification
{
    private User $user;
    private AbstractNotification $notification;
    private array $data;

    public function queue(UserPushSubscription $userPushSubscription, ?Carbon $sendAt = null): void
    {
        if ($userPushSubscription->user_id !== $this->user->id) {
            throw new \LogicException('UserPushSubscription does not belong to User.');
        }

        if (!$sendAt) {
            $sendAt = now();
        }

        UserPushQueue::create([
            'handler_class' => $userPushSubscription->handler_class,
            'user_id' => $this->user->id,
            'user_push_subscription_id' => $userPushSubscription->id,
            'data' => $this->data,
            'send_at' => $sendAt,
        ]);
    }

    public function __construct(User $user, AbstractNotification $notification, array $extraData = [])
    {
        $this->user = $user;
        $this->notification = $notification;

        list($contentType, $contentId) = $notification->getContentTypeId();

        $this->data = array_merge([
            'message' => $notification->getNotificationMessage(),
            'content_type' => $contentType,
            'content_id' => $contentId,
        ], $extraData);
    }
}