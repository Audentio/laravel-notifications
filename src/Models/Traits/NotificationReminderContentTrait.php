<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\NotificationReminder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait NotificationReminderContentTrait
{
    public function notificationReminders(): MorphMany
    {
        return $this->morphMany(NotificationReminder::class, 'content');
    }

    public function getNotificationReminder(string $handlerClass, bool $newIfNotExists = true): ?NotificationReminder
    {
        $query = $this->notificationReminders()->where('handler_class', $handlerClass);

        if ($newIfNotExists) {
            return $query->firstOrNew([
                'content_type' => $this->getContentType(),
                'content_id' => $this->getKey(),
                'handler_class' => $handlerClass
            ]);
        }

        return $query->first();
    }
}