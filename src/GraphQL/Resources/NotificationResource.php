<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Resources;

use App\Models\Notification;
use Audentio\LaravelBase\Utils\ContentTypeUtil;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Resource as GraphQLResource;
use Audentio\LaravelNotifications\Models\Interfaces\NotificationModelInterface;
use GraphQL;

class NotificationResource extends GraphQLResource
{
    public function getExpectedModelClass(): ?string
    {
        return Notification::class;
    }

    public function getOutputFields(string $scope): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::id())],
            'sender' => [
                'type' => GraphQL::type('User'),
            ],
            'content_type' => [
                'type' => GraphQL::type('NotificationContentTypeEnum'),
                'resolve' => function (NotificationModelInterface $notificationModel) {
                    if (!$notificationModel->content_type) {
                        return null;
                    }
                    return ContentTypeUtil::getFriendlyContentTypeName($notificationModel->content_type);
                }
            ],
            'content_id' => ['type' => Type::id()],
            'content' => ['type' => GraphQL::type('NotificationContentUnionType')],
            'message' => Type::methodValue(Type::string(), 'getMessage', [
                'with' => ['content'],
            ]),
            'url' => Type::methodValue(Type::string(), 'getUrl', [
                'with' => ['content'],
            ]),
            'kind' => ['type' => Type::nonNull(GraphQL::type('NotificationKindEnum'))],
            'read_at' => ['type' => Type::timestamp()],
            'created_at' => ['type' => Type::timestamp()],

            'isRead' => Type::methodValue(Type::nonNull(Type::boolean()), 'isRead'),
            'isDismissed' => Type::methodValue(Type::nonNull(Type::boolean()), 'isDismissed'),
        ];
    }

    public function getInputFields(string $scope, bool $update = false): array
    {
        return [];
    }

    public function getCommonFields(string $scope, bool $update = false): array
    {
        return [];
    }

    public function getGraphQLTypeName(): string
    {
        return 'Notification';
    }
}
