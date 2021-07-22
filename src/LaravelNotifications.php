<?php

namespace Audentio\LaravelNotifications;

use Audentio\LaravelNotifications\Jobs\QueueMassNotificationJob;

class LaravelNotifications
{
    const CHANNELS = ['notification', 'email'];

    protected static $runsMigrations = true;

    public static function queueMassNotification(string $notificationClass, ...$arguments): void
    {
        QueueMassNotificationJob::dispatch($notificationClass, $arguments);
    }

    public static function skipMigrations(): bool
    {
        self::$runsMigrations = false;
    }

    public static function runsMigrations(): bool
    {
        return self::$runsMigrations;
    }
}