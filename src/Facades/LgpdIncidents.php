<?php

namespace JeffersonGoncalves\LgpdIncidents\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\LgpdIncidents\LgpdIncidents
 */
class LgpdIncidents extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-lgpd-incidents';
    }
}
