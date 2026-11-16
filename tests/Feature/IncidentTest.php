<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use JeffersonGoncalves\LgpdIncidents\Enums\Risk;
use JeffersonGoncalves\LgpdIncidents\Events\IncidentOpened;
use JeffersonGoncalves\LgpdIncidents\Events\IncidentOverdue;
use JeffersonGoncalves\LgpdIncidents\Facades\LgpdIncidents;
use JeffersonGoncalves\LgpdIncidents\Models\Incident;

it('sets the deadline 3 business days after discovery, skipping weekends and holidays', function () {
    Event::fake([IncidentOpened::class]);

    // Friday 2026-11-13: Mon 16 (1), Tue 17 (2), Wed 18 (3). Discovered Wednesday 18: Thu 19, Fri 20 is a holiday, Mon 23, Tue 24.
    $friday = LgpdIncidents::open('Leaked backup', Carbon::parse('2026-11-13 10:00'));
    $wednesday = LgpdIncidents::open('Phishing', Carbon::parse('2026-11-18 10:00'));

    expect($friday->due_at->toDateTimeString())->toBe('2026-11-18 23:59:59')
        ->and($wednesday->due_at->toDateString())->toBe('2026-11-24');

    Event::assertDispatched(IncidentOpened::class, 2);
});

it('doubles the deadline for small processing agents', function () {
    config()->set('lgpd-incidents.deadline_business_days', 6);

    // 16, 17, 18, 19, (20 is a holiday), 23, 24.
    expect(LgpdIncidents::deadline(Carbon::parse('2026-11-13'))->toDateString())->toBe('2026-11-24');
});

it('owes communication until the ANPD and the data subjects are notified', function () {
    $incident = LgpdIncidents::open('Leaked backup', attributes: ['data_categories' => ['personal', 'financial'], 'affected_subjects' => 120]);

    expect($incident->isPending())->toBeTrue();

    LgpdIncidents::assess($incident, Risk::Relevant);
    LgpdIncidents::notifyAnpd($incident, 'ANPD-2026-0042');

    expect($incident->isPending())->toBeTrue();

    LgpdIncidents::notifySubjects($incident);

    expect($incident->fresh())
        ->isPending()->toBeFalse()
        ->anpd_protocol->toBe('ANPD-2026-0042')
        ->data_categories->toBe(['personal', 'financial']);
});

it('owes nothing once assessed as low risk', function () {
    $incident = LgpdIncidents::assess(LgpdIncidents::open('Lost encrypted laptop'), Risk::Low, 'Disk encrypted, key not exposed.');

    $this->travel(10)->days();

    expect($incident->isOverdue())->toBeFalse()
        ->and(Incident::query()->scopes('pending')->count())->toBe(0);
});

it('flags overdue incidents', function () {
    Event::fake([IncidentOverdue::class]);
    $overdue = LgpdIncidents::open('Leaked backup');
    $done = LgpdIncidents::notifySubjects(LgpdIncidents::notifyAnpd(LgpdIncidents::open('Phishing')));

    $this->travel(10)->days();
    Artisan::call('lgpd:incidents-overdue');

    expect($overdue->isOverdue())->toBeTrue()
        ->and($done->isOverdue())->toBeFalse()
        ->and(Artisan::output())->toContain('1 overdue incident(s).');
    Event::assertDispatched(IncidentOverdue::class, fn (IncidentOverdue $event) => $event->incident->is($overdue));
});

it('requires a risk assessment before closing', function () {
    LgpdIncidents::close(LgpdIncidents::open('Leaked backup'));
})->throws(LogicException::class, 'Assess the risk');

it('keeps records for 5 years', function () {
    $incident = LgpdIncidents::open('Leaked backup');

    expect(fn () => $incident->delete())->toThrow(LogicException::class, '5 years');

    $this->travel(5)->years();
    $this->travel(1)->day();
    $incident->delete();

    expect(Incident::count())->toBe(0);
});
