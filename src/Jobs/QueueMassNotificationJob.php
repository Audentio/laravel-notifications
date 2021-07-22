<?php

namespace Audentio\LaravelNotifications\Jobs;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\AbstractMassNotificationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueueMassNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected string $notificationClass;
    protected array $arguments;

    public function handle(): void
    {
        try {
            $reflector = new \ReflectionClass($this->notificationClass);
        } catch (\ReflectionException $e) {
            return;
        }

        /** @var AbstractMassNotificationInterface|AbstractNotification $notification */
        $notification = $reflector->newInstanceArgs($this->arguments);

        if (!$notification instanceof AbstractMassNotificationInterface) {
            return;
        }

        $users = $notification->getUsers();
        \Notification::send($users, $notification);
    }

    public function __construct(string $notificationClass, array $arguments)
    {
        $this->notificationClass = $notificationClass;
        $this->arguments = $arguments;
    }
}