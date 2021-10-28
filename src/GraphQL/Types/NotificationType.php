<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Types;

use Audentio\LaravelNotifications\GraphQL\Resources\NotificationResource;
use Audentio\LaravelGraphQL\GraphQL\Support\Type as GraphQLType;

class NotificationType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Notification',
        'description' => 'A type'
    ];

    protected function getResourceClassName(): string
    {
        return NotificationResource::class;
    }
}