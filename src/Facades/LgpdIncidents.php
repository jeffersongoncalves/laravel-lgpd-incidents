<?php

namespace JeffersonGoncalves\LgpdIncidents\Facades;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\LgpdIncidents\Enums\Risk;
use JeffersonGoncalves\LgpdIncidents\IncidentManager;
use JeffersonGoncalves\LgpdIncidents\Models\Incident;

/**
 * @method static Incident open(string $title, ?DateTimeInterface $discoveredAt = null, array<string, mixed> $attributes = [])
 * @method static Incident assess(Incident $incident, Risk $risk, ?string $measures = null)
 * @method static Incident notifyAnpd(Incident $incident, ?string $protocol = null, ?DateTimeInterface $at = null)
 * @method static Incident notifySubjects(Incident $incident, ?DateTimeInterface $at = null)
 * @method static Incident close(Incident $incident, ?string $measures = null)
 * @method static CarbonImmutable deadline(DateTimeInterface $discoveredAt)
 *
 * @see IncidentManager
 */
class LgpdIncidents extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IncidentManager::class;
    }
}
