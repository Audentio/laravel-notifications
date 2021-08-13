<?php

namespace Audentio\LaravelNotifications\Console\Commands;

use App\Models\NotificationReminder;
use Audentio\LaravelNotifications\Jobs\DispatchQueuedPushNotificationsJob;
use Audentio\LaravelNotifications\LaravelNotifications;
use Illuminate\Console\Command;

class CronQueueNotificationRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audentio-notifications:cron:queue-notification-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue pending notification remidners';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pendingReminders = NotificationReminder::where('next_send_at', '<', now())->get();

        /** @var NotificationReminder $reminder */
        foreach ($pendingReminders as $reminder) {
            $reminder->queueNotification();
        }

        return 0;
    }
}
