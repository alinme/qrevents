<?php

use App\Models\Event;
use App\Models\User;
use App\Notifications\DailyCleanupDigestNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

test('cleanup digest command notifies configured super admin users when review work is pending', function () {
    CarbonImmutable::setTestNow('2026-03-14 12:00:00');
    Notification::fake();

    config([
        'app.super_admin_emails' => ['admin@example.com'],
        'events.cleanup_policy.locked_candidate_after_days' => 14,
        'events.cleanup_policy.expired_candidate_after_days' => 7,
    ]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);

    Event::factory()->for($owner)->create([
        'name' => 'Digest Review Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'storage_used_bytes' => 2048,
        'upload_count' => 2,
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Digest Approved Event',
        'status' => Event::STATUS_EXPIRED,
        'retention_ends_at' => now()->subDays(10),
        'cleanup_review_state' => 'approved',
        'cleanup_reviewed_at' => now()->subHours(8),
        'storage_used_bytes' => 1024,
        'upload_count' => 1,
    ]);

    $this->artisan('events:send-cleanup-digest')
        ->expectsOutput('Cleanup digest sent.')
        ->expectsOutput('Tracked events: 2')
        ->expectsOutput('Needs review: 1')
        ->expectsOutput('Recent review actions: 1')
        ->expectsOutput('Recipients: 1')
        ->assertSuccessful();

    Notification::assertCount(1);

    CarbonImmutable::setTestNow();
});

test('cleanup digest command can send on-demand mail for configured addresses without user accounts', function () {
    CarbonImmutable::setTestNow('2026-03-14 12:00:00');
    Notification::fake();

    config([
        'app.super_admin_emails' => ['ops@example.com'],
        'events.cleanup_policy.locked_candidate_after_days' => 14,
        'events.cleanup_policy.expired_candidate_after_days' => 7,
    ]);

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);

    Event::factory()->for($owner)->create([
        'name' => 'On Demand Digest Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'storage_used_bytes' => 4096,
        'upload_count' => 4,
    ]);

    $this->artisan('events:send-cleanup-digest')
        ->expectsOutput('Cleanup digest sent.')
        ->expectsOutput('Recipients: 1')
        ->assertSuccessful();

    Notification::assertSentOnDemand(
        DailyCleanupDigestNotification::class,
        function (DailyCleanupDigestNotification $notification, array $channels, object $notifiable): bool {
            $payload = $notification->toArray($notifiable);

            return $channels === ['mail']
                && ($notifiable->routes['mail'] ?? null) === 'ops@example.com'
                && $payload['counts']['review'] === 1;
        },
    );

    CarbonImmutable::setTestNow();
});

test('cleanup digest command stays quiet when there is no actionable cleanup activity', function () {
    CarbonImmutable::setTestNow('2026-03-14 12:00:00');
    Notification::fake();

    config([
        'app.super_admin_emails' => ['admin@example.com'],
        'events.cleanup_policy.locked_candidate_after_days' => 14,
        'events.cleanup_policy.expired_candidate_after_days' => 7,
    ]);

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);

    Event::factory()->for($owner)->create([
        'name' => 'Cooldown Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(2),
        'storage_used_bytes' => 512,
    ]);

    $this->artisan('events:send-cleanup-digest')
        ->expectsOutput('No cleanup activity to report.')
        ->assertSuccessful();

    Notification::assertNothingSent();

    CarbonImmutable::setTestNow();
});
