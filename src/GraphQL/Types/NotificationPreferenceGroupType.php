<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Types;

use Audentio\LaravelNotifications\GraphQL\Resources\NotificationPreferenceGroupResource;
use Audentio\LaravelGraphQL\GraphQL\Support\Type as GraphQLType;

class NotificationPreferenceGroupType extends GraphQLType
{
    protected $attributes = [
        'name' => 'NotificationPreferenceGroup',
        'description' => 'A type'
    ];

    protected function getResourceClassName(): string
    {
        return NotificationPreferenceGroupResource::class;
    }
}