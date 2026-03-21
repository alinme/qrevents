<?php

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

test('regular users cannot access the cleanup admin page', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.cleanup'))
        ->assertForbidden();
});

test('super admins can review cleanup states on the dedicated cleanup page', function () {
    CarbonImmutable::setTestNow('2026-03-14 12:00:00');
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
        'name' => 'Needs Review Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'storage_used_bytes' => 1024,
        'upload_count' => 1,
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Approved Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'cleanup_review_state' => 'approved',
        'cleanup_reviewed_at' => now()->subDay(),
        'storage_used_bytes' => 2048,
        'upload_count' => 2,
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Protected Event',
        'status' => Event::STATUS_EXPIRED,
        'retention_ends_at' => now()->subDays(20),
        'cleanup_review_state' => 'protected',
        'cleanup_reviewed_at' => now()->subDays(2),
        'storage_used_bytes' => 4096,
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Completed Event',
        'status' => Event::STATUS_EXPIRED,
        'retention_ends_at' => now()->subDays(20),
        'cleanup_review_state' => 'completed',
        'cleanup_reviewed_at' => now()->subDay(),
        'storage_used_bytes' => 0,
        'upload_count' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.cleanup'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Cleanup')
            ->where('summary.cleanupPendingReviewCount', 1)
            ->where('summary.cleanupApprovedCount', 1)
            ->where('summary.cleanupProtectedCount', 1)
            ->where('summary.cleanupCompletedCount', 1)
            ->where('adminLinks.cleanup', route('admin.cleanup'))
            ->where(
                'cleanupEvents',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Needs Review Event'
                        && $event['cleanup']['stateCode'] === 'review',
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Approved Event'
                        && $event['cleanup']['stateCode'] === 'approved'
                        && $event['canPurgeMedia'] === true,
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Protected Event'
                        && $event['cleanup']['stateCode'] === 'protected',
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Completed Event'
                        && $event['cleanup']['stateCode'] === 'completed',
                ),
            )
        );

    CarbonImmutable::setTestNow();
});

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
