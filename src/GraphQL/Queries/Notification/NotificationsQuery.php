<?php

declare(strict_types=1);

namespace Audentio\LaravelNotifications\GraphQL\Queries\Notification;

use App\Core;
use App\Models\Notification;
use Audentio\LaravelGraphQL\GraphQL\Definitions\Type;
use Audentio\LaravelGraphQL\GraphQL\Support\Query;
use Audentio\LaravelGraphQL\GraphQL\Traits\FilterableQueryTrait;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as GraphQLType;
use Illuminate\Database\Eloquent\Builder;
use Rebing\GraphQL\Support\Facades\GraphQL;

class NotificationsQuery extends Query
{
    use FilterableQueryTrait;

    protected static NotificationsQuery $instance;

    protected $attributes = [
        'name' => 'NotificationsQuery',
        'description' => 'Retrieve a list of notifications.'
    ];

    public static function getQueryType(): GraphQLType
    {
        return GraphQL::paginate(GraphQL::type('Notification'));
    }

    public static function getFilters(): array
    {
        return [
            'created_at' => [
                'type' => Type::timestamp(),
                'hasOperator' => true
            ],
            'exclude_ids' => [
                'type' => Type::listOf(Type::id()),
                'resolve' => function(Builder $query, $operator, $value) {
                    $query->whereNotIn('id', $value);
                }
            ],
            'isRead' => [
                'type' => Type::boolean(),
                'description' => 'Filter the `isRead` state. Leave blank or NULL to include both read and unread.',
                'hasOperator' => false,
                'resolve' => function (Builder $query, $operator, $value) {
                    if ($value === null) {
                        return;
                    }

                    if ($value) {
                        $query->whereNotNull('read_at');
                    } else {
                        $query->whereNull('read_at');
                    }
                }
            ],
            'isDismissed' => [
                'type' => Type::boolean(),
                'description' => 'Filter the `isDismissed` state. Leave blank or NULL to include both dismissed and undismissed.',
                'hasOperator' => false,
                'resolve' => function (Builder $query, $operator, $value) {
                    if ($value === null) {
                        return;
                    }

                    if ($value) {
                        $query->whereNotNull('dismissed_at');
                    } else {
                        $query->whereNull('dismissed_at');
                    }
                }
            ],
        ];
    }

    public static function getQueryArgs($scope = ''): array
    {
        $args = [
        ];

        self::addFilterArgs($scope, $args);
        self::addPaginationArgs($scope, $args);

        return $args;
    }

    public static function getResolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $instance = self::$instance;
        $fields = $getSelectFields();
        $with = $fields->getRelations();
        $root->with($with);

        if (!Core::isAuthenticated()) {
            $instance->permissionError($info);
        }

        $root->where('user_id', Core::viewer()->id);
        $root->orderBy('created_at', 'desc');
        self::applyFilters($root, $args);

        return self::paginateResults($root, $args);
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $root = Notification::query();
        return self::getResolve($root, $args, $context, $info, $getSelectFields);
    }

    public function __construct()
    {
        self::$instance = $this;
    }
}