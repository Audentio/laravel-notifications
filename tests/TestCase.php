<?php

namespace Audentio\LaravelNotifications\Tests;

use Audentio\LaravelBase\Providers\BaseExtendServiceProvider;
use Audentio\LaravelBase\Providers\LaravelBaseServiceProvider;
use Audentio\LaravelNotifications\LaravelNotifications;
use Audentio\LaravelNotifications\Providers\NotificationServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BaseExtendServiceProvider::class,
            LaravelBaseServiceProvider::class,
            NotificationServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        LaravelNotifications::skipGraphQLSchema();
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('audentioNotifications.push_enabled', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
