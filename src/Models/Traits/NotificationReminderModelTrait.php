<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\NotificationReminder;
use Audentio\LaravelBase\Foundation\Traits\ContentTypeTrait;
use Audentio\LaravelNotifications\LaravelNotifications;
use Audentio\LaravelNotifications\NotificationReminders\AbstractReminder;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;

/**
 * @property Carbon|null $next_send_at
 * @property Carbon|null $last_sent_at
 * @property Carbon|null $due_at
 * @property string $handler_class
 * @property string $content_type
 * @property string $content_id
 * @property array $data
 * @property mixed $content
 * @property array $casts
 */
trait NotificationReminderModelTrait
{
    use ContentTypeTrait;

    public function getHandler(): AbstractReminder
    {
        $className = $this->handler_class;

        /** @var AbstractReminder $instance */
        $instance = new $className($this->due_at);

        return $instance;
    }

    public function queueNotification(): void
    {
        $nextSendAt = $this->getHandler()->getNextSendTime($this->last_sent_at);
        if ($nextSendAt) {
            $this->last_sent_at = now();
            $this->next_send_at = $nextSendAt;
            $this->save();
        } else {
            $this->delete();
        }

        $this->getHandler()->onBeforeNotificationFire($this);
        $this->getHandler()->dismissPreviousNotifications($this);

        $notification = LaravelNotifications::getNotificationInstance($this->getHandler()->getNotificationClassName(),
            $this->content);

        if ($notification instanceof MassNotificationInterface) {
            LaravelNotifications::queueMassNotificationInstance($notification);
        } else {
            $notifiable = $this->content->user ?? null;
            if ($notifiable) {
                $notifiable->notify($notification);
            }
        }
        $this->getHandler()->onAfterNotificationFire($this);
    }

    protected function initializeNotificationReminderModelTrait()
    {
        $this->fillable = array_merge($this->fillable, [
            'handler_class', 'content_type', 'content_id', 'data', 'last_sent_at', 'next_send_at',
        ]);

        $this->casts = array_merge($this->casts, [
            'data' => 'json',
            'last_sent_at' => 'datetime',
            'next_send_at' => 'datetime',
            'due_at' => 'datetime',
        ]);
    }

    protected static function bootNotificationReminderModelTrait()
    {
        static::saving(function (NotificationReminder $reminder) {
            if (!$reminder->exists || $reminder->wasChanged('due_at')) {
                $handler = $reminder->getHandler();

                $reminder->next_send_at = $handler->getNextSendTime($reminder->last_sent_at);
            }
        });
    }
}