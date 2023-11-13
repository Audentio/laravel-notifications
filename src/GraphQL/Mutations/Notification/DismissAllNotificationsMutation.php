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
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use \Closure;
use GraphQL\Type\Definition\Type as GraphqlType;

class DismissAllNotificationsMutation extends Mutation
{
    use ErrorTrait;

    protected $attributes = [
        'name' => 'DismissAllNotificationsMutation',
        'description' => 'Dismiss all notifications'
    ];

    public function args(): array
    {
        return [];
    }

    public function type(): GraphqlType
    {
        return \GraphQL::newObjectType([
            'name' => lcfirst($this->getActionType() . $this->getResource()->getGraphQLTypeName()),
            'fields' => [
                lcfirst($this->getActionType() . $this->getResource()->getGraphQLTypeName()) => [
                    'name' => 'count',
                    'type' => Type::nonNull(Type::int()),
                ],
            ],
        ]);
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $selectFields)
    {
        if (!Core::isAuthenticated()) {
            $this->permissionError($info);
        }

        $count = 0;
        Core::viewer()->notifications()->notDismissed()->chunkById(100, function ($notifications) use (&$count) {
            /** @var Notification $notification */
            foreach ($notifications as $notification) {
                $notification->dismiss();
                $count++;
            }
        }, 'incr_id');

        return [
            'count' => $count
        ];
    }

    protected function getActionType(): string
    {
        return 'dismissAll';
    }

    protected function getResourceClassName(): string
    {
        return NotificationResource::class;
    }
}