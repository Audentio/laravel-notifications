<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Enums;

use Audentio\LaravelGraphQL\GraphQL\Support\Enum;

class PushNotificationHandlerEnum extends Enum
{
    protected $enumObject = true;

    protected $attributes = [
        'name' => 'PushNotificationHandlerEnum',
        'description' => 'An enum type',
        'values' => [],
    ];

    public function __construct()
    {
        $this->attributes['values'] = array_keys(config('audentioNotifications.push_handler_classes'));
    }
}