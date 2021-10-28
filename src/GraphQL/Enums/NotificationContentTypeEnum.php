<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Enums;

use Audentio\LaravelBase\Utils\ContentTypeUtil;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Enums\ContentType\AbstractContentTypeEnum;
use Audentio\LaravelNotifications\LaravelNotifications;

class NotificationContentTypeEnum extends AbstractContentTypeEnum
{
    protected function _getContentTypes(): array
    {
        return LaravelNotifications::getNotificationContentTypes();
    }
}