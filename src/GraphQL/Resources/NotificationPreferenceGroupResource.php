<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Resources;

use App\Models\NotificationPreferenceGroup;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Resource as GraphQLResource;
use GraphQL;

class NotificationPreferenceGroupResource extends GraphQLResource
{
    public function getExpectedModelClass(): ?string
    {
        return NotificationPreferenceGroup::class;
    }

    public function getOutputFields(string $scope): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::id())],
            'name' => Type::methodValue(Type::nonNull(Type::string()), 'getName'),

            'notificationPreferences' => [
                'type' => Type::listOf(GraphQL::type('NotificationPreference')),
            ]
        ];
    }

    public function getInputFields(string $scope, bool $update = false): array
    {
        return [];
    }

    public function getCommonFields(string $scope, bool $update = false): array
    {
        return [];
    }

    public function getGraphQLTypeName(): string
    {
        return 'NotificationPreferenceGroup';
    }
}