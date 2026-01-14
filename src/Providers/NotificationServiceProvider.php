<?php

namespace Audentio\LaravelNotifications\Providers;

use Audentio\LaravelGraphQL\LaravelGraphQL;
use Audentio\LaravelNotifications\Console\Commands\CronQueueNotificationRemindersCommand;
use Audentio\LaravelNotifications\Console\Commands\CronQueuePushNotificationJob;
use Audentio\LaravelNotifications\Console\Commands\DebugPushNotificationJob;
use Audentio\LaravelNotifications\GraphQL\Enums\NotificationChannelEnum;
use Audentio\LaravelNotifications\GraphQL\Enums\NotificationContentTypeEnum;
use Audentio\LaravelNotifications\GraphQL\Enums\NotificationKindEnum;
use Audentio\LaravelNotifications\GraphQL\Enums\PushNotificationHandlerEnum;
use Audentio\LaravelNotifications\GraphQL\Mutations\Notification\DismissAllNotificationsMutation;
use Audentio\LaravelNotifications\GraphQL\Mutations\Notification\DismissNotificationMutation;
use Audentio\LaravelNotifications\GraphQL\Mutations\Notification\MarkReadAllNotificationsMutation;
use Audentio\LaravelNotifications\GraphQL\Mutations\Notification\MarkReadNotificationMutation;
use Audentio\LaravelNotifications\GraphQL\Mutations\Notification\MarkUnreadNotificationMutation;
use Audentio\LaravelNotifications\GraphQL\Mutations\Notification\SendSampleNotificationMutation;
use Audentio\LaravelNotifications\GraphQL\Mutations\UserNotificationPreferenceValue\UpdateViewerNotificationPreferenceValueMutation;
use Audentio\LaravelNotifications\GraphQL\Queries\Notification\NotificationsQuery;
use Audentio\LaravelNotifications\GraphQL\Queries\NotificationPreferenceGroup\NotificationPreferenceGroupsQuery;
use Audentio\LaravelNotifications\GraphQL\Types\NotificationPreferenceGroupType;
use Audentio\LaravelNotifications\GraphQL\Types\NotificationPreferenceType;
use Audentio\LaravelNotifications\GraphQL\Types\NotificationType;
use Audentio\LaravelNotifications\GraphQL\Types\UserNotificationPreferenceValueType;
use Audentio\LaravelNotifications\GraphQL\Types\UserPushSubscriptionType;
use Audentio\LaravelNotifications\GraphQL\UnionTypes\NotificationContentUnionType;
use Audentio\LaravelNotifications\LaravelNotifications;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../../config';
    const MODELS_PATH = __DIR__ . '/../../models';

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/audentioNotifications.php', 'audentioNotifications'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->registerPublishes();
            $this->registerGraphQLSchema();
        }
    }

    protected function registerMigrations(): void
    {
        if (LaravelNotifications::runsMigrations()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

            if (config('audentioNotifications.push_enabled')) {
                $this->loadMigrationsFrom(__DIR__ . '/../../database/push_migrations');
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                DebugPushNotificationJob::class,
                CronQueuePushNotificationJob::class,
                CronQueueNotificationRemindersCommand::class,
            ]);
        }
    }

    protected function registerPublishes(): void
    {
        $this->publishes([
            self::CONFIG_PATH . '/audentioNotifications.php' => config_path('audentioNotifications.php'),
        ], 'audentio-notifications-config');

        $appModelsPath = base_path('app/Models');
        $models = [
            self::MODELS_PATH . '/Notification.php.template' => $appModelsPath . '/Notification.php',

            self::MODELS_PATH . '/NotificationReminder.php.template' => $appModelsPath . '/NotificationReminder.php',

            self::MODELS_PATH . '/UserNotificationPreference.php.template' => $appModelsPath . '/UserNotificationPreference.php',
            self::MODELS_PATH . '/NotificationPreference.php.template' => $appModelsPath . '/NotificationPreference.php',
            self::MODELS_PATH . '/NotificationPreferenceGroup.php.template' => $appModelsPath . '/NotificationPreferenceGroup.php',
        ];

        if (config('audentioNotifications.push_enabled')) {
            $models = array_merge($models, [
                self::MODELS_PATH . '/UserPushQueue.php.template' => $appModelsPath . '/UserPushQueue.php',
                self::MODELS_PATH . '/UserPushSubscription.php.template' => $appModelsPath . '/UserPushSubscription.php',
            ]);
        }

        $this->publishes($models, 'audentio-notifications-models');
    }

    protected function registerGraphQLSchema(): void
    {
        if (LaravelNotifications::addsGraphQLSchema()) {
            $schema = [
                'types' => [
                    'NotificationPreferenceGroupType' => NotificationPreferenceGroupType::class,
                    'NotificationPreferenceType' => NotificationPreferenceType::class,
                    'NotificationType' => NotificationType::class,
                    'UserNotificationPreferenceValueType' => UserNotificationPreferenceValueType::class,

                    // Union
                    'NotificationContentUnionType' => NotificationContentUnionType::class,

                    // Enums
                    'NotificationChannelEnum' => NotificationChannelEnum::class,
                    'NotificationContentTypeEnum' => NotificationContentTypeEnum::class,
                    'NotificationKindEnum' => NotificationKindEnum::class,
                ],
                'queries' => [
                    'notifications' => NotificationsQuery::class,
                    'notificationPreferenceGroups' => NotificationPreferenceGroupsQuery::class,
                ],
                'mutations' => [
                    'dismissNotification' => DismissNotificationMutation::class,
                    'dismissAllNotifications' => DismissAllNotificationsMutation::class,
                    'markReadNotification' => MarkReadNotificationMutation::class,
                    'markUnreadNotification' => MarkUnreadNotificationMutation::class,
                    'markReadAllNotifications' => MarkReadAllNotificationsMutation::class,
                    'sendSampleNotification' => SendSampleNotificationMutation::class,

                    'updateViewerNotificationPreferenceValue' => UpdateViewerNotificationPreferenceValueMutation::class,
                ],
            ];

            if (config('audentioNotifications.push_enabled')) {
                $schema['types'] = array_merge($schema['types'], [
                    'UserPushSubscriptionType' => UserPushSubscriptionType::class,

                    // Enums
                    'PushNotificationHandlerEnum' => PushNotificationHandlerEnum::class,
                ]);
            }

            $overrides = config('audentioNotifications.graphQL_schema_overrides');
            foreach ($schema as $schemaType => &$values) {
                foreach ($values as $key => &$value) {
                    if (isset($overrides[$schemaType][$value])) {
                        $value = $overrides[$schemaType][$value];
                    } else if (isset($overrides[$schemaType][$key])) {
                        $value = $overrides[$schemaType][$key];
                    }
                }
            }

            LaravelGraphQL::registerTypes($schema['types']);
            LaravelGraphQL::registerQueries($schema['queries']);
            LaravelGraphQL::registerMutations($schema['mutations']);
        }
    }
}
