<?php

namespace JeffersonGoncalves\LgpdIncidents\Tests;

use JeffersonGoncalves\LgpdIncidents\LgpdIncidentsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LgpdIncidentsServiceProvider::class,
        ];
    }
}
