<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Enums;

use Audentio\LaravelGraphQL\GraphQL\Support\Enum;
use Audentio\LaravelNotifications\LaravelNotifications;

class NotificationChannelEnum extends Enum
{
    protected $enumObject = true;

    protected $attributes = [
        'name' => 'NotificationChannel',
        'description' => 'An enum type',
        'values' => [],
    ];

    public function __construct()
    {
        $this->attributes['values'] = LaravelNotifications::getEnabledChannels();
    }
}