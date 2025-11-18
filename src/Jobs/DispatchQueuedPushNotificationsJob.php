<?php

namespace Audentio\LaravelNotifications\Jobs;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Audentio\LaravelNotifications\PushHandlers\AbstractPushHandler;
use Audentio\LaravelNotifications\Utils\TimerUtil;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchQueuedPushNotificationsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected string $notificationClass;
    protected array $arguments;

    public function handle(): void
    {
        while (true) {
            $handlerClasses = \DB::table('user_push_queues')
                ->selectRaw('DISTINCT(`handler_class`)')
                ->where('send_at', '<', now())
                ->get()->pluck('handler_class')->all();

            if (empty($handlerClasses)) {
                return;
            }

            foreach ($handlerClasses as $handlerClass) {
                if (!class_exists($handlerClass)) {
                    \DB::table('user_push_queues')->where('handler_class', $handlerClass)->delete();
                }

                /** @var AbstractPushHandler $handler */
                $handler = new $handlerClass;
                $handler->runBatch();
                break;
            }
        }
    }
}
