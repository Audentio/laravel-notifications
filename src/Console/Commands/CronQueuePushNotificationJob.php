<?php

namespace Audentio\LaravelNotifications\Console\Commands;

use Audentio\LaravelNotifications\Jobs\DispatchQueuedPushNotificationsJob;
use Illuminate\Console\Command;

class CronQueuePushNotificationJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audentio-notifications:cron:queue-push-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        if (\DB::table('user_push_queues')->where('send_at', '<', now())->count() > 0) {
            DispatchQueuedPushNotificationsJob::dispatchSync();
        }

        return 0;
    }
}
