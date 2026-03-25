<?php

use App\Models\Event;
use App\Models\EventGuestLedgerReminderLog;
use App\Models\EventGuestParty;
use App\Models\User;
use App\Notifications\EventGuestLedgerExportReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('sends guest ledger export reminders once per retention snapshot', function () {
    Notification::fake();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-14 12:00:00', 'Europe/Bucharest'));

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Benjamin and Rebecca Wedding',
        'is_paid' => true,
        'timezone' => 'Europe/Bucharest',
        'retention_ends_at' => CarbonImmutable::parse('2026-03-21 23:59:59', 'Europe/Bucharest'),
    ]);

    EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'attendance_status' => 'accepted',
        'confirmed_attendees_count' => 4,
        'invited_attendees_count' => 4,
        'gift_type' => 'money',
        'gift_currency' => 'EUR',
        'gift_amount' => 400,
    ]);

    EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Ionescu',
        'attendance_status' => 'pending',
        'invited_attendees_count' => 3,
    ]);

    $this->artisan('events:send-guest-ledger-export-reminders')
        ->assertExitCode(0);

    Notification::assertSentTo($owner, EventGuestLedgerExportReminderNotification::class, function (
        EventGuestLedgerExportReminderNotification $notification,
    ): bool {
        $payload = $notification->toArray(new stdClass);

        return $payload['daysBeforeExpiry'] === 7
            && $payload['eventName'] === 'Benjamin and Rebecca Wedding';
    });

    expect(EventGuestLedgerReminderLog::query()->count())->toBe(1)
        ->and(EventGuestLedgerReminderLog::query()->first()?->reminder_key)->toBe('7_days');

    $this->artisan('events:send-guest-ledger-export-reminders')
        ->assertExitCode(0);

    expect(EventGuestLedgerReminderLog::query()->count())->toBe(1);

    $event->forceFill([
        'retention_ends_at' => CarbonImmutable::parse('2026-04-13 23:59:59', 'Europe/Bucharest'),
    ])->save();

    $this->artisan('events:send-guest-ledger-export-reminders')
        ->assertExitCode(0);

    Notification::assertSentTo($owner, EventGuestLedgerExportReminderNotification::class, function (
        EventGuestLedgerExportReminderNotification $notification,
    ): bool {
        $payload = $notification->toArray(new stdClass);

        return $payload['daysBeforeExpiry'] === 30
            && $payload['eventName'] === 'Benjamin and Rebecca Wedding';
    });

    expect(EventGuestLedgerReminderLog::query()->count())->toBe(2)
        ->and(EventGuestLedgerReminderLog::query()->where('reminder_key', '30_days')->exists())->toBeTrue();
});
