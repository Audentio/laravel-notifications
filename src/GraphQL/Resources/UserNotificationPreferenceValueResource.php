<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Resources;

use App\Models\UserNotificationPreference;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Resource as GraphQLResource;
use GraphQL;

class UserNotificationPreferenceValueResource extends GraphQLResource
{
    public function getExpectedModelClass(): ?string
    {
        return UserNotificationPreference::class;
    }

    public function getOutputFields(string $scope): array
    {
        return [];
    }

    public function getInputFields(string $scope, bool $update = false): array
    {
        return [];
    }

    public function getCommonFields(string $scope, bool $update = false): array
    {
        return [
            'notification_preference_id' => ['type' => Type::nonNull(Type::id())],
            'disabled_channels' => ['type' => Type::listOf(GraphQL::type('NotificationChannelEnum'))],
        ];
    }

    public function getGraphQLTypeName(): string
    {
        return 'UserNotificationPreferenceValue';
    }
}