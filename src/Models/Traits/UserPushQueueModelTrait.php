<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\User;
use App\Models\UserPushQueue;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\Jobs\DispatchQueuedPushNotificationsJob;
use Audentio\LaravelNotifications\LaravelNotifications;
use Audentio\LaravelNotifications\PushHandlers\AbstractPushHandler;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UserPushQueueModelTrait
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userPushSubscription(): BelongsTo
    {
        return $this->belongsTo(UserPushSubscription::class);
    }

    public function logSuccess(): void
    {
        $this->userPushSubscription->logSuccess();
        $this->delete();
    }

    public function logDelay(): void
    {
        $newSendAt = $this->calculateNewSendAt();

        if (!$newSendAt) {
            $this->logCancel();
            return;
        }

        $this->fail_count = $this->fail_count + 1;
        $this->last_fail_at = now();
        $this->send_at = $newSendAt;

        $this->save();
    }

    public function logCancel(): void
    {
        $this->delete();
    }

    public function logCancelPushSubscription(): void
    {
        /** @var UserPushSubscription|null $userPushSubscription */
        $userPushSubscription = $this->userPushSubscription;
        $userPushSubscription->logCancel();
    }

    protected function calculateNewSendAt(): ?Carbon
    {
        switch ($this->fail_count) {
            case 0: $interval = CarbonInterval::createFromDateString('5 minutes');break;
            case 1: $interval = CarbonInterval::createFromDateString('15 minutes');break;
            case 2: $interval = CarbonInterval::createFromDateString('1 hour');break;
            case 3: $interval = CarbonInterval::createFromDateString('2 hours');break;
            default: return null;
        }

        return now()->add($interval);
    }

    protected function initializeUserPushQueueModelTrait(): void
    {
        $this->fillable = array_merge([
            'handler_class', 'user_id', 'user_push_subscription_id', 'data', 'send_at'
        ], $this->fillable);

        $this->casts = array_merge([
            'data' => 'json',
            'send_at' => 'datetime',
            'last_fail_at' => 'datetime',
        ], $this->casts);

        $this->attributes = array_merge([
            'fail_count' => 0,
        ], $this->attributes);

        $this->with = array_merge([
            'user', 'userPushSubscription',
        ], $this->with);
    }

    protected static function bootUserPushQueueModelTrait(): void
    {
        static::created(function (UserPushQueue $userPushQueue) {
            if (config('queue.default') !== 'sync') {
                // Only automatically queue job if it's not being run synchronously.
                DispatchQueuedPushNotificationsJob::dispatch();
            }
        });
    }
}