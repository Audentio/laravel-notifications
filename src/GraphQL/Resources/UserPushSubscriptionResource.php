<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Resources;

use App\Models\UserPushSubscription;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Resource as GraphQLResource;
use GraphQL;

class UserPushSubscriptionResource extends GraphQLResource
{
    public function getExpectedModelClass(): ?string
    {
        return UserPushSubscription::class;
    }

    public function getOutputFields(string $scope): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::id())],
        ];
    }

    public function getInputFields(string $scope, bool $update = false): array
    {
        return [];
    }

    public function getCommonFields(string $scope, bool $update = false): array
    {
        return [
            'handler' => [
                'type' => Type::nonNull(GraphQL::type('PushNotificationHandlerEnum')),
                'resolve' => function (UserPushSubscription $userPushSubscription) {
                    return $userPushSubscription->getHandler()->getIdentifier();
                }
            ],
            'token' => ['type' => Type::nonNull(Type::string())],
        ];
    }

    public function getGraphQLTypeName(): string
    {
        return 'UserPushSubscriptionType';
    }
}