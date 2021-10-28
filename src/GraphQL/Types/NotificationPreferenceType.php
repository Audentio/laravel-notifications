<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Types;

use Audentio\LaravelNotifications\GraphQL\Resources\NotificationPreferenceResource;
use Audentio\LaravelGraphQL\GraphQL\Support\Type as GraphQLType;

class NotificationPreferenceType extends GraphQLType
{
    protected $attributes = [
        'name' => 'NotificationPreference',
        'description' => 'A type'
    ];

    protected function getResourceClassName(): string
    {
        return NotificationPreferenceResource::class;
    }
}