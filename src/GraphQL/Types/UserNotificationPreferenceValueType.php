<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Types;

use Audentio\LaravelNotifications\GraphQL\Resources\UserNotificationPreferenceValueResource;
use Audentio\LaravelGraphQL\GraphQL\Support\Type as GraphQLType;

class UserNotificationPreferenceValueType extends GraphQLType
{
    protected $attributes = [
        'name' => 'UserNotificationPreferenceValue',
        'description' => 'A type'
    ];

    protected function getResourceClassName(): string
    {
        return UserNotificationPreferenceValueResource::class;
    }
}