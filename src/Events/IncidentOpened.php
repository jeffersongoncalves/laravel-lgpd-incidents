<?php

namespace JeffersonGoncalves\LgpdIncidents\Events;

use Illuminate\Foundation\Events\Dispatchable;
use JeffersonGoncalves\LgpdIncidents\Models\Incident;

class IncidentOpened
{
    use Dispatchable;

    public function __construct(public Incident $incident) {}
}
