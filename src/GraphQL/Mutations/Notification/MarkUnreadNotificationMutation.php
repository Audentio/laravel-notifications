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
use GraphQL\Type\Definition\ResolveInfo;

class MarkUnreadNotificationMutation extends Mutation
{
    use ErrorTrait;

    protected $attributes = [
        'name' => 'MarkUnreadNotificationMutation',
        'description' => 'Mark a notification as unread.',
    ];

    public function args(): array
    {
        return [
            'notification' => [
                'rules' => ['required'],
                'type' => \GraphQL::newInputObjectType([
                    'name' => $this->getActionType()
                        . $this->getResource()->getGraphQLTypeName()
                        . 'Data',
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
        $notification = Core::viewer()
            ->notifications()
            ->find($args['notification']['id']);

        if (!$notification) {
            $this->notFoundError($info);
        }

        $notification->markUnread();

        return [
            'notification' => $notification,
        ];
    }

    protected function getActionType(): string
    {
        return 'markUnread';
    }

    protected function getResourceClassName(): string
    {
        return NotificationResource::class;
    }
}