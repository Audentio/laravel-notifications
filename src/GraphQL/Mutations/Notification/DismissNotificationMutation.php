<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Mutations\Notification;

use App\Core;
use Audentio\LaravelNotifications\GraphQL\Resources\NotificationResource;
use App\Models\Notification;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Mutation;
use Audentio\LaravelGraphQL\GraphQL\Traits\ErrorTrait;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use \Closure;

class DismissNotificationMutation extends Mutation
{
    use ErrorTrait;

    protected $attributes = [
        'name' => 'DismissNotificationMutation',
        'description' => 'Dismiss a notification'
    ];

    public function args(): array
    {
        return [
            'notification' => [
                'rules' => ['required'],
                'type' => new InputObjectType([
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
        $notification = Core::viewer()->notifications()->find($args['notification']['id']);
        if (!$notification) {
            $this->notFoundError($info);
        }

        $notification->dismiss();

        return [
            'notification' => $notification
        ];
    }

    protected function getActionType(): string
    {
        return 'dismiss';
    }

    protected function getResourceClassName(): string
    {
        return NotificationResource::class;
    }
}