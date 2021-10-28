<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Mutations\Notification;

use App\Core;
use Audentio\LaravelNotifications\Notifications\Sample\SampleNotification;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Mutation;
use Audentio\LaravelGraphQL\GraphQL\Traits\ErrorTrait;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as GraphQLType;
use Notification;

class SendSampleNotificationMutation extends Mutation
{
    use ErrorTrait;

    protected $attributes = [
        'name' => 'SendSampleNotificationMutation',
        'description' => 'Send a sample push notification.'
    ];

    public function type(): GraphqlType
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [];
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $selectFields)
    {
        if (!Core::isAuthenticated()) {
            $this->permissionError($info);
        }

        Notification::sendNow(Core::viewer(), new SampleNotification);

        return true;
    }

    protected function getActionType(): string
    {
        return 'send';
    }

    protected function getResourceClassName(): string
    {
        return '';
    }
}