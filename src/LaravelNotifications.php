<?php

namespace Audentio\LaravelNotifications;

use App\Models\User;
use Audentio\LaravelBase\Utils\ContentTypeUtil;
use Audentio\LaravelNotifications\Jobs\QueueMassNotificationJob;
use Audentio\LaravelNotifications\Notifications\AbstractMassNotification;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Audentio\LaravelNotifications\PushHandlers\AbstractPushHandler;

class LaravelNotifications
{
    protected static bool $runsMigrations = true;
    protected static bool $addsGraphQLSchema = true;

    /** @var AbstractPushHandler[] */
    protected static array $pushHandlers = [];

    public static function skipMigrations(): void
    {
        self::$runsMigrations = false;
    }

    public static function runsMigrations(): bool
    {
        return self::$runsMigrations;
    }

    public static function addsGraphQLSchema(): bool
    {
        return self::$addsGraphQLSchema;
    }

    public static function skipGraphQLSchema(): void
    {
        self::$addsGraphQLSchema = false;
    }

    public static function getNotificationKinds(): array
    {
        $kinds = config('audentioNotifications.notification_kinds') ?? [];

        $kinds[] = 'sample';

        return $kinds;
    }

    public static function getNotificationContentTypes(): array
    {
        $contentTypes = array_keys(ContentTypeUtil::getContentTypeField('isNotificationContent')) ?? [];

        if (!in_array(User::class, $contentTypes)) {
            $contentTypes[] = 'User';
        }

        return $contentTypes;
    }

    public static function getEnabledChannels(): array
    {
        $channels = ['notification', 'email'];

        if (config('audentioNotifications.push.enabled')) {
            $channels[] = 'push';
        }

        $customChannels = config('audentioNotifications.custom_notification_channel_names') ?? [];
        foreach ($customChannels as $channel) {
            $channels[] = $channel;
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
        $notification = call_user_func_array(['self', 'getNotificationInstance'], array_merge([$notificationClass], $arguments));

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

        $notificationModelClass = config('audentioNotifications.notification_model_class');
        $notificationModelClass::where($conditionals)->chunkById(100, function ($notifications) {
            $notifications->map(function ($notification) {
                $notification->delete();
            });
        });
    }

    public static function massDismiss($contentType, $contentId, ?string $className = null): void
    {
        $conditionals = [
            ['content_type', $contentType],
            ['content_id', $contentId],
        ];
        if ($className) {
            $conditionals['type'] = $className;
        }

        $notificationModelClass = config('audentioNotifications.notification_model_class');
        $notificationModelClass::where($conditionals)->chunkById(100, function ($notifications) {
            $notifications->map(function ($notification) {
                $notification->dismiss();
            });
        });
    }

}
