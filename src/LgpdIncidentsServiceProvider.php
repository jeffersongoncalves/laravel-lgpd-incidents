<?php

namespace JeffersonGoncalves\LgpdIncidents;

use JeffersonGoncalves\LgpdIncidents\Commands\OverdueCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LgpdIncidentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lgpd-incidents')
            ->hasConfigFile()
            ->hasMigrations(['create_lgpd_incidents_table', 'add_complement_columns_to_lgpd_incidents_table'])
            ->hasCommand(OverdueCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(IncidentManager::class);
    }
}
