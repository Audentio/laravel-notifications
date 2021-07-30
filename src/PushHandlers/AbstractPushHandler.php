<?php

namespace Audentio\LaravelNotifications\PushHandlers;

use App\Models\User;
use App\Models\UserPushQueue;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\PushResponse;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractPushHandler
{
    public function createUserPushSubscription(string $userId, string $token): UserPushSubscription
    {
        $userPushSubscription = UserPushSubscription::firstOrNew([
            'user_id' => $userId,
            'token' => $token,
            'handler_class' => $this->getHandlerClassName()
        ]);

        $userPushSubscription->data = [];
        $userPushSubscription->save();

        return $userPushSubscription;
    }

    public function runBatch(): void
    {
        $userPushQueue = $this->getBatchUserPushQueues();
        $pushResponse = $this->dispatchPushNotifications($userPushQueue);
        $pushResponse->handleLogs();
    }

    protected function getBatchUserPushQueues(): Collection
    {
        return UserPushQueue::where('handler_class', $this->getHandlerClassName())
            ->where(function ($query) {
                $query->whereNull('send_at')
                    ->orWhere('send_at', '<', now());
            })->limit($this->getQueueBatchSize())->get();
    }

    protected function getHandlerClassName(): string
    {
        return get_class($this);
    }

    protected function logSuccessForUserIdAndToken(string $userId, string $token): void
    {
        $userPushSubscription = UserPushSubscription::where([
            ['user_id', $userId],
            ['token', $token],
            ['handler_class', $this->getHandlerClassName()],
        ])->first();

        if ($userPushSubscription) {
            $this->logSuccessForUserPushSubscription($userPushSubscription);
        }
    }

    protected function logSuccessForUserPushSubscription(UserPushSubscription $userPushSubscription): void
    {
        $userPushSubscription->fail_count = 0;
        $userPushSubscription->last_success_at = now();
        $userPushSubscription->save();
    }

    protected function logFailureForUserIdAndToken(string $userId, string $token): void
    {
        $userPushSubscription = UserPushSubscription::where([
            ['user_id', $userId],
            ['token', $token],
            ['handler_class', $this->getHandlerClassName()],
        ])->first();

        if ($userPushSubscription) {
            $this->logFailureForUserPushSubscription($userPushSubscription);
        }
    }

    protected function logFailureForUserPushSubscription(UserPushSubscription $userPushSubscription): void
    {
        $userPushSubscription->refresh();

        $userPushSubscription->fail_count = $userPushSubscription->fail_count + 1;
        $userPushSubscription->last_fail_at = now();
        $userPushSubscription->save();
    }

    abstract public function getIdentifier(): string;
    abstract public function dispatchPushNotifications(Collection $userPushQueues): PushResponse;
    abstract protected function getQueueBatchSize(): int;
}