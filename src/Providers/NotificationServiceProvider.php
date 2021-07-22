<?php

namespace Audentio\LaravelNotifications\Providers;

use Audentio\LaravelNotifications\LaravelNotifications;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/audentioNotifications.php', 'audentioNotifications'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->registerPublishes();
        }
    }

    protected function registerMigrations(): void
    {
        if (LaravelNotifications::runsMigrations()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }
    }

    protected function registerPublishes(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/audentioNotifications.php' => config_path('audentioNotifications.php'),
        ]);
    }


}