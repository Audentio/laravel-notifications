<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelNotifications\LaravelNotifications;

trait NotificationContentTrait
{
    protected static function bootNotificationContentTrait(): void
    {
        static::deleted(function(AbstractModel $model) {
            LaravelNotifications::massDelete($model->getContentType(), $model->getKey());
        });
    }
}