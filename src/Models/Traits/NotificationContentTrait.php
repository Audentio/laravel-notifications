<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelNotifications\LaravelNotifications;

trait NotificationContentTrait
{
    public static function contentTypeFields__notificationContentTrait(): array
    {
        return [
            'isNotificationContent' => true,
        ];
    }

    protected static function bootNotificationContentTrait(): void
    {
        static::deleted(function(AbstractModel $model) {
            LaravelNotifications::massDelete($model->getContentType(), $model->getKey());
        });
    }
}