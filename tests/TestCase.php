<?php

namespace JeffersonGoncalves\LgpdIncidents\Tests;

use JeffersonGoncalves\BrazilHolidays\BrazilHolidaysServiceProvider;
use JeffersonGoncalves\LgpdIncidents\LgpdIncidentsServiceProvider;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            BrazilHolidaysServiceProvider::class,
            LgpdIncidentsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadPackageMigrations(LgpdIncidentsServiceProvider::class);
    }
}
