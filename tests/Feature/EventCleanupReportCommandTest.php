<?php

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;

test('cleanup report command summarizes cleanup states and can show details', function () {
    CarbonImmutable::setTestNow('2026-03-14 12:00:00');
    config([
        'events.cleanup_policy.locked_candidate_after_days' => 14,
        'events.cleanup_policy.expired_candidate_after_days' => 7,
    ]);

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);

    Event::factory()->for($owner)->create([
        'name' => 'Review Report Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'storage_used_bytes' => 1024,
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Approved Report Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'cleanup_review_state' => 'approved',
        'cleanup_reviewed_at' => now()->subDay(),
        'storage_used_bytes' => 2048,
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Completed Report Event',
        'status' => Event::STATUS_EXPIRED,
        'retention_ends_at' => now()->subDays(20),
        'cleanup_review_state' => 'completed',
        'cleanup_reviewed_at' => now()->subDay(),
        'storage_used_bytes' => 0,
    ]);

    $this->artisan('events:cleanup-report --detailed')
        ->expectsOutputToContain('Cleanup report')
        ->expectsOutputToContain('Needs review: 1')
        ->expectsOutputToContain('Approved: 1')
        ->expectsOutputToContain('Completed: 1')
        ->expectsOutputToContain('Review Report Event')
        ->assertExitCode(0);

    $this->artisan('events:cleanup-report --state=approved')
        ->expectsOutputToContain('Filter: approved')
        ->expectsOutputToContain('Approved: 1')
        ->assertExitCode(0);

    CarbonImmutable::setTestNow();
});
