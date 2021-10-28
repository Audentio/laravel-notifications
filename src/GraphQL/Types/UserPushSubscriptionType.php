<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Types;

use Audentio\LaravelNotifications\GraphQL\Resources\UserPushSubscriptionResource;
use Audentio\LaravelGraphQL\GraphQL\Support\Type as GraphQLType;

class UserPushSubscriptionType extends GraphQLType
{
    protected $attributes = [
        'name' => 'UserPushSubscription',
        'description' => 'A type'
    ];

    protected function getResourceClassName(): string
    {
        return UserPushSubscriptionResource::class;
    }
}