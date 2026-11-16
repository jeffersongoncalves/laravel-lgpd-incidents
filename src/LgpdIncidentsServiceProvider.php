<?php

namespace JeffersonGoncalves\LgpdIncidents;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LgpdIncidentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-lgpd-incidents')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
