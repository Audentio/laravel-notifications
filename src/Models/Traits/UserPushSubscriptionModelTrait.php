<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\User;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\LaravelNotifications;
use Audentio\LaravelNotifications\PushHandlers\AbstractPushHandler;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UserPushSubscriptionModelTrait
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getHandler(): AbstractPushHandler
    {
        return new $this->handler_class;
    }

    public function logSuccess(): void
    {
        $this->last_success_at = now();
        $this->fail_count = 0;
        $this->save();
    }

    public function logCancel(): void
    {
        $this->delete();
    }

    protected function initializeUserPushSubscriptionModelTrait(): void
    {
        $this->fillable = array_merge([
            'user_id', 'handler_class', 'token', 'data'
        ], $this->fillable);

        $this->casts = array_merge([
            'data' => 'json',
            'last_fail_at' => 'datetime',
            'last_success_at' => 'datetime',
        ], $this->casts);

        $this->attributes = array_merge([
            'fail_count' => 0,
        ], $this->attributes);
    }

    protected static function bootUserPushSubscriptionModelTrait()
    {
        static::deleted(function(UserPushSubscription $userPushSubscription) {
            \DB::table('user_push_queues')->where('user_push_subscription_id', $userPushSubscription->id)->delete();
        });
    }
}