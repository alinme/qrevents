<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('regular users cannot access the admin area', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.overview'))
        ->assertForbidden();
});

test('super admins can review platform users events and billing queues', function () {
    CarbonImmutable::setTestNow('2026-03-14 11:00:00');
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
    ]);
    $owner = User::factory()->create([
        'name' => 'Studio Owner',
        'email' => 'owner@example.com',
    ]);
    $idleUser = User::factory()->create([
        'name' => 'Idle User',
        'email' => 'idle@example.com',
    ]);
    $plan = Plan::factory()->create([
        'name' => 'Business 49 EUR',
        'currency' => 'EUR',
        'price_cents' => 4900,
    ]);

    $lockedEvent = Event::factory()->for($owner)->for($plan)->create([
        'name' => 'Locked City Wedding',
        'status' => Event::STATUS_LOCKED,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'paid_at' => null,
        'billing_note' => 'Customer promised transfer tomorrow.',
        'onboarding_completed_at' => now(),
        'upload_count' => 8,
        'storage_limit_bytes' => 10737418240,
        'storage_used_bytes' => 3221225472,
    ]);
    $paidEvent = Event::factory()->for($owner)->for($plan)->create([
        'name' => 'Paid Garden Party',
        'status' => Event::STATUS_LIVE,
        'is_paid' => true,
        'payment_due_at' => now()->subDays(4),
        'paid_at' => now()->subDay(),
        'onboarding_completed_at' => now(),
        'upload_count' => 12,
        'storage_limit_bytes' => 21474836480,
        'storage_used_bytes' => 4294967296,
    ]);

    EventAsset::factory()->for($lockedEvent)->for($owner)->create([
        'moderation_status' => 'processing',
    ]);
    EventAsset::factory()->count(2)->for($paidEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.overview'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Overview')
            ->where('summary.totalUsers', 3)
            ->where('summary.businessCount', 1)
            ->where('summary.totalEvents', 2)
            ->where('summary.totalUploads', 3)
            ->where('summary.pendingModerationCount', 1)
            ->where('summary.unpaidEventCount', 1)
            ->where('summary.overdueEventCount', 0)
            ->where('summary.lockedEventCount', 1)
            ->where('summary.totalAllocatedStorageBytes', 32212254720)
            ->where('summary.totalUsedStorageBytes', 7516192768)
            ->where('summary.totalFreeStorageBytes', 24696061952)
            ->where('summary.storageCleanupCandidateCount', 1)
            ->where('adminLinks.users', route('admin.users'))
            ->where('backNavigation.href', route('dashboard.account'))
            ->where(
                'recentUsers',
                fn ($users): bool => collect($users)->contains(
                    fn (array $user): bool => $user['email'] === 'owner@example.com'
                        && $user['eventCount'] === 2
                        && $user['unpaidEventCount'] === 1
                        && $user['lockedEventCount'] === 1
                        && $user['storage']['usedBytes'] === 7516192768,
                ),
            )
            ->where(
                'recentEvents',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Locked City Wedding'
                        && $event['ownerEmail'] === 'owner@example.com'
                        && $event['billingLabel'] === 'Payment overdue'
                        && $event['storage']['usedBytes'] === 3221225472,
                ),
            )
            ->where('billingQueue.0.name', 'Locked City Wedding')
            ->where('billingQueue.0.queueLabel', 'Locked')
            ->where('billingQueue.0.billingNote', 'Customer promised transfer tomorrow.')
        );

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Users')
            ->where('users.0.email', 'idle@example.com')
            ->where(
                'users',
                fn ($users): bool => collect($users)->contains(
                    fn (array $user): bool => $user['email'] === 'owner@example.com'
                        && $user['latestEventName'] === 'Paid Garden Party'
                        && $user['storage']['freeBytes'] === 24696061952,
                ),
            )
        );

    $this->actingAs($admin)
        ->get(route('admin.events'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Events')
            ->where(
                'events',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Paid Garden Party'
                        && $event['ownerEmail'] === 'owner@example.com'
                        && $event['links']['settings'] === route('events.settings', $paidEvent)
                        && $event['storage']['usedBytes'] === 4294967296,
                ),
            )
        );

    $this->actingAs($admin)
        ->get(route('admin.billing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Billing')
            ->where('attentionEvents.0.name', 'Locked City Wedding')
            ->where('attentionEvents.0.canPurgeMedia', false)
            ->where('attentionEvents.0.cleanup.stateLabel', 'Cooldown')
            ->where('attentionEvents.0.hasExportArchive', false)
            ->where('attentionEvents.0.storage.usedBytes', 3221225472)
            ->where(
                'recentPayments',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Paid Garden Party'
                        && $event['ownerEmail'] === 'owner@example.com'
                        && $event['storage']['usedBytes'] === 4294967296,
                ),
            )
        );

    CarbonImmutable::setTestNow();
});

test('super admin can clear a stored export archive with typed confirmation', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);
    Storage::fake('public');

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Export Cleanup Event',
        'media_export_status' => 'ready',
        'media_export_disk' => 'public',
        'media_export_path' => 'exports/export-cleanup-event.zip',
        'media_export_token' => 'token-123',
        'media_export_requested_at' => now()->subHour(),
        'media_export_started_at' => now()->subMinutes(50),
        'media_export_completed_at' => now()->subMinutes(45),
    ]);

    Storage::disk('public')->put('exports/export-cleanup-event.zip', 'zip-content');

    $this->actingAs($admin)
        ->post(route('admin.events.cleanup', $event), [
            'action' => 'clear_export',
            'confirmation_name' => 'Export Cleanup Event',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Stored export archive cleared.');

    $event->refresh();

    expect($event->media_export_status)->toBeNull()
        ->and($event->media_export_path)->toBeNull()
        ->and($event->media_export_disk)->toBeNull();

    Storage::disk('public')->assertMissing('exports/export-cleanup-event.zip');
});

test('super admin can purge locked event media and reclaim storage', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);
    Storage::fake('public');

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Locked Cleanup Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
        'upload_count' => 2,
        'storage_used_bytes' => 6000,
        'media_export_status' => 'ready',
        'media_export_disk' => 'public',
        'media_export_path' => 'exports/locked-cleanup-event.zip',
        'media_export_token' => 'token-456',
    ]);

    $photo = EventAsset::factory()->for($event)->for($owner)->create([
        'disk' => 'public',
        'path' => 'events/locked-cleanup/photo.jpg',
        'thumbnail_path' => 'events/locked-cleanup/photo-thumb.jpg',
        'preview_path' => 'events/locked-cleanup/photo-preview.jpg',
        'size_bytes' => 2000,
    ]);
    $video = EventAsset::factory()->for($event)->for($owner)->create([
        'disk' => 'public',
        'kind' => 'video',
        'path' => 'events/locked-cleanup/video.mp4',
        'video_thumbnail_path' => 'events/locked-cleanup/video-thumb.jpg',
        'video_preview_path' => 'events/locked-cleanup/video-preview.mp4',
        'size_bytes' => 4000,
    ]);

    foreach ([
        'events/locked-cleanup/photo.jpg',
        'events/locked-cleanup/photo-thumb.jpg',
        'events/locked-cleanup/photo-preview.jpg',
        'events/locked-cleanup/video.mp4',
        'events/locked-cleanup/video-thumb.jpg',
        'events/locked-cleanup/video-preview.mp4',
        'exports/locked-cleanup-event.zip',
    ] as $path) {
        Storage::disk('public')->put($path, 'content');
    }

    $this->actingAs($admin)
        ->post(route('admin.events.cleanup-review', $event), [
            'review_state' => 'approved',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Cleanup approved for this event.');

    $this->actingAs($admin)
        ->post(route('admin.events.cleanup', $event), [
            'action' => 'purge_media',
            'confirmation_name' => 'Locked Cleanup Event',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $event->refresh();

    expect(EventAsset::query()->whereKey($photo->id)->exists())->toBeFalse()
        ->and(EventAsset::query()->whereKey($video->id)->exists())->toBeFalse()
        ->and($event->upload_count)->toBe(0)
        ->and($event->storage_used_bytes)->toBe(0)
        ->and($event->media_export_path)->toBeNull()
        ->and($event->media_export_status)->toBeNull();

    foreach ([
        'events/locked-cleanup/photo.jpg',
        'events/locked-cleanup/photo-thumb.jpg',
        'events/locked-cleanup/photo-preview.jpg',
        'events/locked-cleanup/video.mp4',
        'events/locked-cleanup/video-thumb.jpg',
        'events/locked-cleanup/video-preview.mp4',
        'exports/locked-cleanup-event.zip',
    ] as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

test('media purge requires explicit cleanup approval even for old locked events', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $event = Event::factory()->create([
        'name' => 'Approval Required Event',
        'status' => Event::STATUS_LOCKED,
        'payment_due_at' => now()->subDays(30),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.billing'))
        ->post(route('admin.events.cleanup', $event), [
            'action' => 'purge_media',
            'confirmation_name' => 'Approval Required Event',
        ])
        ->assertRedirect(route('admin.billing'))
        ->assertSessionHasErrors('action');
});

test('super admin can protect and clear cleanup review state', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $event = Event::factory()->create([
        'status' => Event::STATUS_LOCKED,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.events.cleanup-review', $event), [
            'review_state' => 'protected',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Cleanup protection enabled for this event.');

    $event->refresh();

    expect($event->cleanup_review_state)->toBe('protected')
        ->and($event->cleanup_reviewed_at)->not->toBeNull();

    $this->actingAs($admin)
        ->post(route('admin.events.cleanup-review', $event), [
            'review_state' => 'clear',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Cleanup review cleared for this event.');

    $event->refresh();

    expect($event->cleanup_review_state)->toBeNull()
        ->and($event->cleanup_reviewed_at)->toBeNull();
});

test('cleanup requires the exact event name confirmation', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $event = Event::factory()->create([
        'name' => 'Needs Exact Match',
        'status' => Event::STATUS_LOCKED,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.billing'))
        ->post(route('admin.events.cleanup', $event), [
            'action' => 'purge_media',
            'confirmation_name' => 'Wrong Name',
        ])
        ->assertRedirect(route('admin.billing'))
        ->assertSessionHasErrors('confirmation_name');
});
