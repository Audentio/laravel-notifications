<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Enums;

use Audentio\LaravelGraphQL\GraphQL\Support\Enum;

class NotificationKindEnum extends Enum
{
    protected $enumObject = true;

    protected $attributes = [
        'name' => 'NotificationKindEnum',
        'description' => 'An enum type',
        'values' => [],
    ];

    public function __construct()
    {
        $kinds = config('audentioNotifications.notification_kinds');
        if (!empty($kinds)) {
            $this->attributes['values'] = $kinds;
        }
    }
}