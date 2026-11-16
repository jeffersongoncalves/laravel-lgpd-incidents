<?php

namespace JeffersonGoncalves\LgpdIncidents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\LgpdIncidents\Enums\Risk;
use LogicException;

/**
 * @property string $title
 * @property Carbon $discovered_at
 * @property Carbon $due_at
 * @property Risk|null $risk
 * @property list<string>|null $data_categories
 * @property Carbon|null $anpd_notified_at
 * @property Carbon|null $subjects_notified_at
 * @property Carbon|null $closed_at
 * @property Carbon $created_at
 */
class Incident extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('lgpd-incidents.table', 'lgpd_incidents');
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'discovered_at' => 'datetime',
            'due_at' => 'datetime',
            'risk' => Risk::class,
            'data_categories' => 'array',
            'affected_subjects' => 'integer',
            'anpd_notified_at' => 'datetime',
            'subjects_notified_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Incident $incident): void {
            $years = (int) config('lgpd-incidents.keep_years', 5);

            if ($incident->created_at->isAfter(now()->subYears($years))) {
                throw new LogicException("Incident records must be kept for {$years} years (Res. CD/ANPD 15/2024, art. 10).");
            }
        });
    }

    /**
     * Still owes a communication: not assessed as low risk and the ANPD or the data subjects not notified yet.
     */
    public function isPending(): bool
    {
        return $this->closed_at === null
            && $this->risk !== Risk::Low
            && ($this->anpd_notified_at === null || $this->subjects_notified_at === null);
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_at->isPast();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('closed_at')
            ->where(fn (Builder $q) => $q->whereNull('risk')->orWhere('risk', '!=', Risk::Low->value))
            ->where(fn (Builder $q) => $q->whereNull('anpd_notified_at')->orWhereNull('subjects_notified_at'));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->scopes('pending')->where('due_at', '<', now());
    }
}
