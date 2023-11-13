<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Mutations\Notification;

use App\Core;
use Audentio\LaravelNotifications\GraphQL\Resources\NotificationResource;
use App\Models\Notification;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Mutation;
use Audentio\LaravelGraphQL\GraphQL\Traits\ErrorTrait;
use Closure;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class MarkReadNotificationMutation extends Mutation
{
    use ErrorTrait;

    protected $attributes = [
        'name' => 'MarkReadNotificationMutation',
        'description' => 'Mark a notification as read.'
    ];

    public function args(): array
    {
        return [
            'notification' => [
                'rules' => ['required'],
                'type' => \GraphQL::newInputObjectType([
                    'name' => $this->getActionType() . $this->getResource()->getGraphQLTypeName() . 'Data',
                    'fields' => [
                        'id' => [
                            'type' => Type::nonNull(Type::id()),
                            'rules' => ['required'],
                        ],
                    ],
                ]),
            ],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $selectFields)
    {
        if (!Core::isAuthenticated()) {
            $this->permissionError($info);
        }

        /** @var Notification $notification */
        $notification = Core::viewer()->unreadNotifications()->find($args['notification']['id']);
        if (!$notification) {
            $this->notFoundError($info);
        }

        $notification->markRead();

        return [
            'notification' => $notification,
        ];
    }

    protected function getActionType(): string
    {
        return 'mark';
    }

    protected function getResourceClassName(): string
    {
        return NotificationResource::class;
    }
}