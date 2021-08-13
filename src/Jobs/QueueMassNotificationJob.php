<?php

namespace Audentio\LaravelNotifications\Jobs;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueueMassNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /** @var MassNotificationInterface|AbstractNotification */
    protected MassNotificationInterface $notification;

    public function handle(): void
    {
        $users = $this->notification->getUsers();
        \Notification::send($users, $this->notification);
    }

    public function __construct(MassNotificationInterface $notification)
    {
        $this->notification = $notification;
    }
}