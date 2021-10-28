<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Resources;

use App\Models\NotificationPreference;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Resource as GraphQLResource;
use GraphQL;

class NotificationPreferenceResource extends GraphQLResource
{
    public function getExpectedModelClass(): ?string
    {
        return NotificationPreference::class;
    }

    public function getOutputFields(string $scope): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::id())],
            'name' => Type::methodValue(Type::nonNull(Type::string()), 'getName'),
            'available_channels' => [
                'type' => Type::listOf(GraphQL::type('NotificationChannelEnum')),
            ],
            'required_channels' => [
                'type' => Type::listOf(GraphQL::type('NotificationChannelEnum')),
            ],
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
        return 'NotificationPreference';
    }
}