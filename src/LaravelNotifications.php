<?php

namespace Audentio\LaravelNotifications;

use App\Models\User;
use Audentio\LaravelNotifications\Jobs\QueueMassNotificationJob;
use Audentio\LaravelNotifications\Notifications\AbstractMassNotification;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Audentio\LaravelNotifications\PushHandlers\AbstractPushHandler;

class LaravelNotifications
{
    protected static bool $runsMigrations = true;

    /** @var AbstractPushHandler[] */
    protected static array $pushHandlers = [];

    public static function skipMigrations(): bool
    {
        self::$runsMigrations = false;
    }

    public static function runsMigrations(): bool
    {
        return self::$runsMigrations;
    }

    public static function getEnabledChannels(): array
    {
        $channels = ['notification', 'email'];

        if (config('audentioNotifications.push.enabled')) {
            $channels[] = 'push';
        }

        return $channels;
    }

    public static function getPushHandler(string $handlerName): ?AbstractPushHandler
    {
        if (!isset(self::$pushHandlers[$handlerName])) {
            $handlerClass = config('audentioNotifications.push_handler_classes.' . $handlerName);
            if ($handlerClass && class_exists($handlerClass)) {
                self::$pushHandlers[$handlerName] = new $handlerClass;
            } else {
                self::$pushHandlers[$handlerName] = null;
            }
        }

        return self::$pushHandlers[$handlerName];
    }

    public static function registerUserPushSubscription(string $handlerName, User $user, string $token): void
    {
        $handler = self::getPushHandler($handlerName);
        if (!$handler) {
            throw new \InvalidArgumentException('Invalid push handler: ' . $handlerName);
        }

        $handler->createUserPushSubscription($user->id, $token);
    }

    public static function getNotificationInstance(string $notificationClass, ...$arguments): AbstractNotification
    {
        $reflector = new \ReflectionClass($notificationClass);

        /** @var AbstractNotification $notification */
        $notification = $reflector->newInstanceArgs($arguments);

        return $notification;
    }

    public static function queueMassNotification(string $notificationClass, ...$arguments): void
    {
        $notification = self::getNotificationInstance($notificationClass, $arguments);

        if (!$notification instanceof MassNotificationInterface) {
            return;
        }

        self::queueMassNotificationInstance($notification);
    }

    public static function queueMassNotificationInstance(MassNotificationInterface $notification): void
    {
        QueueMassNotificationJob::dispatch($notification);
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