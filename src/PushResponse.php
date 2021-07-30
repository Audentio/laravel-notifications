<?php

namespace Audentio\LaravelNotifications;

use App\Models\UserPushQueue;
use Illuminate\Support\Collection;

class PushResponse
{
    /** @var Collection|UserPushQueue[] */
    private Collection $userPushQueues;

    private array $successIds = [];
    private array $delayPushIds = [];
    private array $cancelPushIds = [];
    private array $cancelPushSubscriptionIds = [];

    public function getSuccessIds(): array
    {
        return $this->successIds;
    }

    public function getDelayPushIds(): array
    {
        return $this->delayPushIds;
    }

    public function getCancelPushIds(): array
    {
        return $this->cancelPushIds;
    }

    public function getCancelPushSubscriptionIds(): array
    {
        return $this->cancelPushSubscriptionIds;
    }

    /** @return Collection|UserPushQueue[] */
    public function getSuccessUserPushQueues(): Collection
    {
        return $this->userPushQueues->filter(function(UserPushQueue $userPushQueue) {
            return in_array($userPushQueue->id, $this->successIds);
        });
    }

    /** @return Collection|UserPushQueue[] */
    public function getDelayPushUserPushQueues(): Collection
    {
        return $this->userPushQueues->filter(function(UserPushQueue $userPushQueue) {
            return in_array($userPushQueue->id, $this->delayPushIds);
        });
    }

    /** @return Collection|UserPushQueue[] */
    public function getCancelPushUserPushQueues(): Collection
    {
        return $this->userPushQueues->filter(function(UserPushQueue $userPushQueue) {
            return in_array($userPushQueue->id, $this->cancelPushIds);
        });
    }

    /** @return Collection|UserPushQueue[] */
    public function getCancelPushSubscriptionUserPushQueues(): Collection
    {
        return $this->userPushQueues->filter(function(UserPushQueue $userPushQueue) {
            return in_array($userPushQueue->id, $this->cancelPushSubscriptionIds);
        });
    }

    public function logSuccessId(string $id): void
    {
        $this->successIds[] = $id;
    }

    public function logSuccessIds(array $ids): void
    {
        foreach ($ids as $id) {
            $this->logSuccessId($id);
        }
    }

    public function logDelayPushId(string $id): void
    {
        $this->delayPushIds[] = $id;
    }

    public function logDelayPushIds(array $ids): void
    {
        foreach ($ids as $id) {
            $this->logDelayPushId($id);
        }
    }

    public function logCancelPushId(string $id): void
    {
        $this->cancelPushIds[] = $id;
    }

    public function logCancelPushIds(array $ids): void
    {
        foreach ($ids as $id) {
            $this->logCancelPushId($id);
        }
    }

    public function logCancelPushSubscriptionId(string $id): void
    {
        $this->cancelPushSubscriptionIds[] = $id;
    }

    public function logCancelPushSubscriptionIds(array $ids): void
    {
        foreach ($ids as $id) {
            $this->logCancelPushSubscriptionId($id);
        }
    }

    public function handleLogs(): void
    {
        foreach ($this->getSuccessUserPushQueues() as $userPushQueue) {
            $userPushQueue->logSuccess();
        }

        foreach ($this->getDelayPushUserPushQueues() as $userPushQueue) {
            $userPushQueue->logDelay();
        }

        foreach ($this->getCancelPushUserPushQueues() as $userPushQueue) {
            $userPushQueue->logCancel();
        }

        foreach ($this->getCancelPushSubscriptionUserPushQueues() as $userPushQueue) {
            $userPushQueue->logCancelPushSubscription();
        }
        /*
    private array $cancelPushIds = [];
    private array $cancelPushSubscriptionIds = [];*/

    }

    public function __construct(Collection $userPushQueues)
    {
        $this->userPushQueues = $userPushQueues;
    }
}