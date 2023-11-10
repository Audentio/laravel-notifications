<?php

namespace Audentio\LaravelNotifications\GraphQL\UnionTypes;

use Audentio\LaravelGraphQL\GraphQL\Definitions\UnionTypes\ContentType\AbstractContentUnionType;
use Audentio\LaravelNotifications\LaravelNotifications;

class NotificationContentUnionType extends AbstractContentUnionType
{
    public $name = 'NotificationContentUnionType';

    protected function _getContentTypes(): array
    {
        return LaravelNotifications::getNotificationContentTypes();
    }
}