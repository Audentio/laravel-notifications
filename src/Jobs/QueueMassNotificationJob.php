<?php

namespace Audentio\LaravelNotifications\Jobs;

use App\Core;
use App\Models\User;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Audentio\LaravelNotifications\Utils\TimerUtil;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Shopify\Rest\Admin2022_04\Collect;

class QueueMassNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 1;

    /** @var MassNotificationInterface|AbstractNotification */
    protected MassNotificationInterface $notification;

    /** @var Collection|User[] */
    protected Collection $remainingUsers;

    protected int $limit = 100;

    public function handle(): void
    {
        $remainingUsers = $this->remainingUsers;

        $timer = new TimerUtil(45);

        $count = 0;
        foreach ($remainingUsers as $key => $user) {
            $count++;
            $remainingUsers->forget($key);
            try {
                \Notification::send($user, $this->notification);
            } catch (\Throwable $e) {
                // Intentionally ignoring errors here so notifications don't resend.
                Core::captureException($e);
            }
            if ($count > $this->limit || $timer->isLimitReached()) {
                break;
            }
        }

        if ($remainingUsers->isEmpty()) {
            return;
        }
        QueueMassNotificationJob::dispatch($this->notification, $remainingUsers);
    }

    public function __construct(MassNotificationInterface $notification, ?Collection $remainingUsers = null)
    {
        $this->notification = $notification;

        if ($remainingUsers) {
            $this->remainingUsers = $remainingUsers;
        } else {
            $this->remainingUsers = $this->notification->getUsers();
        }
    }
}
