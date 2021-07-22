<?php

namespace Audentio\LaravelNotifications;

use Audentio\LaravelNotifications\Jobs\QueueMassNotificationJob;

class LaravelNotifications
{
    const CHANNELS = ['notification', 'email'];

    protected static $runsMigrations = true;

    public static function skipMigrations(): bool
    {
        self::$runsMigrations = false;
    }

    public static function runsMigrations(): bool
    {
        return self::$runsMigrations;
    }

    public static function queueMassNotification(string $notificationClass, ...$arguments): void
    {
        QueueMassNotificationJob::dispatch($notificationClass, $arguments);
    }

    public static function massDelete($contentType, $contentId, ?string $className = null): void
    {
        $conditionals = [
            ['content_type', $contentType],
            ['content_id', $contentId],
        ];
        if ($className) {
            $conditionals['type'] = $className;
        }

        \DB::table('notifications')->where($conditionals)->delete();
    }

}