<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Resources;

use App\Models\Notification;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Resource as GraphQLResource;
use GraphQL;

class NotificationResource extends GraphQLResource
{
    public function getExpectedModelClass(): ?string
    {
        return Notification::class;
    }

    public function getOutputFields(string $scope): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::id())],
            'sender' => [
                'type' => GraphQL::type('User'),
            ],
            'content_type' => ['type' => GraphQL::type('NotificationContentTypeEnum')],
            'content_id' => ['type' => Type::id()],
            'content' => ['type' => GraphQL::type('NotificationContent')],
            'message' => Type::methodValue(Type::string(), 'getMessage', [
                'with' => ['content'],
            ]),
            'url' => Type::methodValue(Type::string(), 'getUrl', [
                'with' => ['content'],
            ]),
            'kind' => ['type' => Type::nonNull(GraphQL::type('NotificationKindEnum'))],
            'read_at' => ['type' => Type::timestamp()],
            'created_at' => ['type' => Type::timestamp()],

            'isRead' => Type::methodValue(Type::nonNull(Type::boolean()), 'isRead'),
            'isDismissed' => Type::methodValue(Type::nonNull(Type::boolean()), 'isDismissed'),
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
        return 'Notification';
    }
}
