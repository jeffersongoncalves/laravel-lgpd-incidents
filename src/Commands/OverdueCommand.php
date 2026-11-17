<?php

namespace JeffersonGoncalves\LgpdIncidents\Commands;

use Illuminate\Console\Command;
use JeffersonGoncalves\LgpdIncidents\Events\IncidentComplementOverdue;
use JeffersonGoncalves\LgpdIncidents\Events\IncidentOverdue;
use JeffersonGoncalves\LgpdIncidents\Models\Incident;

class OverdueCommand extends Command
{
    protected $signature = 'lgpd:incidents-overdue';

    protected $description = 'Dispatch IncidentOverdue for incidents past the ANPD/data subject notification deadline';

    public function handle(): int
    {
        $count = 0;

        Incident::query()->scopes('overdue')->each(function (Incident $incident) use (&$count): void {
            event(new IncidentOverdue($incident));
            $this->warn("#{$incident->getKey()} {$incident->title}: due {$incident->due_at->toDateTimeString()}");
            $count++;
        });

        $this->info("{$count} overdue incident(s).");

        $complements = 0;

        Incident::query()->scopes('complementOverdue')->each(function (Incident $incident) use (&$complements): void {
            event(new IncidentComplementOverdue($incident));
            $this->warn("#{$incident->getKey()} {$incident->title}: ANPD complement due {$incident->complement_due_at?->toDateTimeString()}");
            $complements++;
        });

        if ($complements > 0) {
            $this->info("{$complements} overdue ANPD complement(s).");
        }

        return self::SUCCESS;
    }
}
