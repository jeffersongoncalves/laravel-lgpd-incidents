<?php

namespace JeffersonGoncalves\LgpdIncidents;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use JeffersonGoncalves\BrazilHolidays\BrazilHolidays;
use JeffersonGoncalves\LgpdIncidents\Enums\Risk;
use JeffersonGoncalves\LgpdIncidents\Events\IncidentOpened;
use JeffersonGoncalves\LgpdIncidents\Models\Incident;
use LogicException;

class IncidentManager
{
    public function __construct(protected BrazilHolidays $holidays) {}

    /**
     * Register an incident. The deadline counts business days from when you learned that it affected personal data.
     *
     * @param  array<string, mixed>  $attributes  description, occurred_at, data_categories, affected_subjects, measures
     */
    public function open(string $title, ?DateTimeInterface $discoveredAt = null, array $attributes = []): Incident
    {
        $discoveredAt ??= now();

        $incident = Incident::create([
            ...$attributes,
            'title' => $title,
            'discovered_at' => $discoveredAt,
            'due_at' => $this->deadline($discoveredAt),
        ]);

        event(new IncidentOpened($incident));

        return $incident;
    }

    /**
     * Record the risk assessment. Low risk: no communication owed (keep the reasoning in measures).
     */
    public function assess(Incident $incident, Risk $risk, ?string $measures = null): Incident
    {
        return $this->update($incident, ['risk' => $risk, 'measures' => $measures ?? $incident->getAttribute('measures')]);
    }

    public function notifyAnpd(Incident $incident, ?string $protocol = null, ?DateTimeInterface $at = null): Incident
    {
        return $this->update($incident, ['anpd_notified_at' => $at ?? now(), 'anpd_protocol' => $protocol]);
    }

    public function notifySubjects(Incident $incident, ?DateTimeInterface $at = null): Incident
    {
        return $this->update($incident, ['subjects_notified_at' => $at ?? now()]);
    }

    public function close(Incident $incident, ?string $measures = null): Incident
    {
        if ($incident->risk === null) {
            throw new LogicException("Assess the risk of incident [{$incident->getKey()}] before closing it.");
        }

        return $this->update($incident, ['closed_at' => now(), 'measures' => $measures ?? $incident->getAttribute('measures')]);
    }

    /**
     * End of the Nth business day after the discovery (weekends and Brazilian holidays skipped).
     */
    public function deadline(DateTimeInterface $discoveredAt): CarbonImmutable
    {
        return $this->holidays->addBusinessDays($discoveredAt, (int) config('lgpd-incidents.deadline_business_days', 3))->endOfDay();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function update(Incident $incident, array $attributes): Incident
    {
        $incident->forceFill($attributes)->save();

        return $incident;
    }
}
