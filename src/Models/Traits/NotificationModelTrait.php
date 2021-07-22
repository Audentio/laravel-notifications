<?php

namespace Audentio\LaravelNotifications\Models\Traits;

trait NotificationModelTrait
{
    protected function initializeNotificationModelTrait(): void
    {
        $this->fillable = array_merge($this->fillable, [
            'id',
            'type',
            'user_id',
            'content_type',
            'content_id',
            'data',
            'is_important',
            'is_system',
        ]);

        $this->casts = array_merge($this->casts, [
            'data' => 'json',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ]);
    }
}