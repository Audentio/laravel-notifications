<?php

namespace Audentio\LaravelNotifications\Jobs;

use App\Core;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueueMassNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 1;

    /** @var MassNotificationInterface|AbstractNotification */
    protected MassNotificationInterface $notification;

    public function handle(): void
    {
        $users = $this->notification->getUsers();

        try {
            \Notification::send($users, $this->notification);
        } catch (\Throwable $e) {
            // Intentionally ignoring errors here so notifications don't resend.
            Core::captureException($e);
        }
    }

    public function __construct(MassNotificationInterface $notification)
    {
        $this->notification = $notification;
    }
}
