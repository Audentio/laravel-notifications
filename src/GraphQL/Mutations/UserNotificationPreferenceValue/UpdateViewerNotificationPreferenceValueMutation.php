<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Mutations\UserNotificationPreferenceValue;

use App\Core;
use Audentio\LaravelNotifications\GraphQL\Resources\UserNotificationPreferenceValueResource;
use App\Models\NotificationPreference;
use App\Models\UserNotificationPreference;
use Audentio\LaravelGraphQL\GraphQL\Support\Mutation;
use Audentio\LaravelGraphQL\GraphQL\Traits\ErrorTrait;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;

class UpdateViewerNotificationPreferenceValueMutation extends Mutation
{
    use ErrorTrait;

    protected $attributes = [
        'name' => 'UpdateViewerNotificationPreferenceValueMutation',
        'description' => 'Update the viewers notification preferences.'
    ];

    protected function removeUpdateResourceIDField(): bool
    {
        return true;
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $selectFields)
    {
        if (!Core::isAuthenticated()) {
            $this->permissionError($info);
        }

        /** @var NotificationPreference $notificationPreference */
        $notificationPreference = NotificationPreference::query()
            ->find($args['userNotificationPreferenceValue']['notification_preference_id']);

        if (!$notificationPreference) {
            $this->notFoundError($info);
        }

        /** @var UserNotificationPreference $userNotificationPreference */
        $userNotificationPreference = Core::viewer()->userNotificationPreferences()
            ->firstOrNew([
                'notification_preference_id' => $args['userNotificationPreferenceValue']['notification_preference_id']
            ]);

        $disabledChannels = $args['userNotificationPreferenceValue']['disabled_channels'] ?? [];

        if (empty($disabledChannels)) {
            if ($userNotificationPreference->exists) {
                $userNotificationPreference->delete();
            }

            $userNotificationPreference = $notificationPreference->getDefaultUserNotificationPreferenceValue();
        } else {
            $userNotificationPreference->disabled_channels = $disabledChannels;
            $userNotificationPreference->save();
        }

        return [
            'userNotificationPreferenceValue' => $userNotificationPreference,
        ];
    }

    protected function getActionType(): string
    {
        return 'update';
    }

    protected function getResourceClassName(): string
    {
        return UserNotificationPreferenceValueResource::class;
    }
}