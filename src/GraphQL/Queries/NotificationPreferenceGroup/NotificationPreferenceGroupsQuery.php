<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Queries\NotificationPreferenceGroup;

use App\Models\NotificationPreferenceGroup;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Query;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as GraphQLType;
use Rebing\GraphQL\Support\Facades\GraphQL;

class NotificationPreferenceGroupsQuery extends Query
{
    protected static NotificationPreferenceGroupsQuery $instance;

    protected $attributes = [
        'name' => 'NotificationPreferenceGroupsQuery',
        'description' => 'Retrieve a list of notification preference groups.'
    ];

    public static function getQueryType(): GraphQLType
    {
        return Type::listOf(GraphQL::type('NotificationPreferenceGroup'));
    }

    public static function getQueryArgs($scope = ''): array
    {
        return [];
    }

    public static function getResolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $fields = $getSelectFields();
        $with = $fields->getRelations();
        $root->with($with);

        return $root->get();
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $root = NotificationPreferenceGroup::query();
        return self::getResolve($root, $args, $context, $info, $getSelectFields);
    }

    public function __construct()
    {
        self::$instance = $this;
    }
}