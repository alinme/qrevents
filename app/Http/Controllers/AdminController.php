<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunEventCleanupRequest;
use App\Http\Requests\UpdateEventCleanupReviewRequest;
use App\Http\Requests\UpsertPlanRequest;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Overview', [
            'summary' => $this->summary(),
            'adminNavigation' => $this->adminNavigation(),
            'adminLinks' => $this->adminLinks(),
            'backNavigation' => $this->dashboardBackNavigation($request),
            'sidebarLabel' => 'Admin',
            'recentUsers' => $this->usersList(limit: 8),
            'recentEvents' => $this->eventRows(limit: 8),
            'billingQueue' => $this->billingAttentionRows(limit: 8),
        ]);
    }

    public function users(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Users', [
            'summary' => $this->summary(),
            'adminNavigation' => $this->adminNavigation(),
            'adminLinks' => $this->adminLinks(),
            'backNavigation' => $this->dashboardBackNavigation($request),
            'sidebarLabel' => 'Admin',
            'users' => $this->usersList(),
        ]);
    }

    public function events(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Events', [
            'summary' => $this->summary(),
            'adminNavigation' => $this->adminNavigation(),
            'adminLinks' => $this->adminLinks(),
            'backNavigation' => $this->dashboardBackNavigation($request),
            'sidebarLabel' => 'Admin',
            'events' => $this->eventRows(),
        ]);
    }

    public function billing(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Billing', [
            'summary' => $this->summary(),
            'adminNavigation' => $this->adminNavigation(),
            'adminLinks' => $this->adminLinks(),
            'backNavigation' => $this->dashboardBackNavigation($request),
            'sidebarLabel' => 'Admin',
            'attentionEvents' => $this->billingAttentionRows(),
            'recentPayments' => $this->recentPaymentRows(),
        ]);
    }

    public function plans(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Plans', [
            'summary' => $this->summary(),
            'adminNavigation' => $this->adminNavigation(),
            'adminLinks' => $this->adminLinks(),
            'backNavigation' => $this->dashboardBackNavigation($request),
            'sidebarLabel' => 'Admin',
            'plans' => $this->planRows(),
        ]);
    }

    public function cleanup(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Cleanup', [
            'summary' => $this->summary(),
            'adminNavigation' => $this->adminNavigation(),
            'adminLinks' => $this->adminLinks(),
            'backNavigation' => $this->dashboardBackNavigation($request),
            'sidebarLabel' => 'Admin',
            'cleanupEvents' => $this->cleanupRows(),
        ]);
    }

    public function cleanupEvent(RunEventCleanupRequest $request, Event $event): RedirectResponse
    {
        $action = (string) $request->string('action');

        return match ($action) {
            'clear_export' => $this->handleClearExportCleanup($event),
            'purge_media' => $this->handlePurgeMediaCleanup($event),
            default => back()->with('error', 'Unknown cleanup action.'),
        };
    }

    public function updateCleanupReview(UpdateEventCleanupReviewRequest $request, Event $event): RedirectResponse
    {
        $reviewState = (string) $request->string('review_state');

        $event->forceFill([
            'cleanup_review_state' => $reviewState === 'clear' ? null : $reviewState,
            'cleanup_reviewed_at' => $reviewState === 'clear' ? null : now(),
        ])->save();

        return back()->with('success', match ($reviewState) {
            'approved' => 'Cleanup approved for this event.',
            'protected' => 'Cleanup protection enabled for this event.',
            default => 'Cleanup review cleared for this event.',
        });
    }

    public function storePlan(UpsertPlanRequest $request): RedirectResponse
    {
        $plan = DB::transaction(function () use ($request): Plan {
            $payload = $this->planPayload($request);

            $plan = Plan::query()->create($payload);
            $this->syncDefaultPlanFlag($plan, (bool) $payload['is_default']);

            return $plan->refresh();
        });

        return back()->with('success', "{$plan->name} package created.");
    }

    public function updatePlan(UpsertPlanRequest $request, Plan $plan): RedirectResponse
    {
        DB::transaction(function () use ($request, $plan): void {
            $payload = $this->planPayload($request);

            $plan->forceFill($payload)->save();
            $this->syncDefaultPlanFlag($plan, (bool) $payload['is_default']);
        });

        return back()->with('success', "{$plan->fresh()->name} package updated.");
    }

    private function assertSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->canAccessAdmin(), 403);
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $totalAllocatedStorageBytes = (int) (Event::query()->sum('storage_limit_bytes') ?? 0);
        $totalUsedStorageBytes = (int) (Event::query()->sum('storage_used_bytes') ?? 0);

        return [
            'totalUsers' => User::query()->count(),
            'businessCount' => User::query()->whereHas('events')->count(),
            'totalEvents' => Event::query()->count(),
            'totalPlans' => Plan::query()->count(),
            'activePlanCount' => Plan::query()->where('is_active', true)->count(),
            'defaultPlanCount' => Plan::query()->where('is_default', true)->count(),
            'totalUploads' => EventAsset::query()->count(),
            'pendingModerationCount' => EventAsset::query()->where('moderation_status', 'processing')->count(),
            'unpaidEventCount' => Event::query()->where('is_paid', false)->count(),
            'overdueEventCount' => Event::query()
                ->where('is_paid', false)
                ->whereNotNull('payment_due_at')
                ->where('payment_due_at', '<', now())
                ->where('status', '!=', Event::STATUS_LOCKED)
                ->count(),
            'lockedEventCount' => Event::query()->where('status', Event::STATUS_LOCKED)->count(),
            'cleanupPendingReviewCount' => Event::query()
                ->where(function ($query): void {
                    $query
                        ->where('status', Event::STATUS_LOCKED)
                        ->orWhere('status', Event::STATUS_EXPIRED);
                })
                ->whereNull('cleanup_review_state')
                ->count(),
            'cleanupApprovedCount' => Event::query()->where('cleanup_review_state', 'approved')->count(),
            'cleanupProtectedCount' => Event::query()->where('cleanup_review_state', 'protected')->count(),
            'cleanupCompletedCount' => Event::query()->where('cleanup_review_state', 'completed')->count(),
            'totalAllocatedStorageBytes' => $totalAllocatedStorageBytes,
            'totalUsedStorageBytes' => $totalUsedStorageBytes,
            'totalFreeStorageBytes' => max(0, $totalAllocatedStorageBytes - $totalUsedStorageBytes),
            'storageCleanupCandidateCount' => Event::query()
                ->whereIn('status', [Event::STATUS_LOCKED, Event::STATUS_EXPIRED])
                ->where('storage_used_bytes', '>', 0)
                ->count(),
        ];
    }

    /**
     * @return list<array{title: string, href: string}>
     */
    private function adminNavigation(): array
    {
        return [
            [
                'title' => 'Overview',
                'href' => route('admin.overview'),
            ],
            [
                'title' => 'Users',
                'href' => route('admin.users'),
            ],
            [
                'title' => 'Events',
                'href' => route('admin.events'),
            ],
            [
                'title' => 'Plans',
                'href' => route('admin.plans'),
            ],
            [
                'title' => 'Billing',
                'href' => route('admin.billing'),
            ],
            [
                'title' => 'Cleanup',
                'href' => route('admin.cleanup'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function adminLinks(): array
    {
        return [
            'overview' => route('admin.overview'),
            'users' => route('admin.users'),
            'events' => route('admin.events'),
            'plans' => route('admin.plans'),
            'plansStore' => route('admin.plans.store'),
            'billing' => route('admin.billing'),
            'cleanup' => route('admin.cleanup'),
            'dashboard' => route('dashboard'),
        ];
    }

    /**
     * @return array{title: string, href: string}
     */
    private function dashboardBackNavigation(Request $request): array
    {
        $user = $request->user();

        if (
            $user !== null
            && $user->canAccessAdmin()
        ) {
            return [
                'title' => 'Account',
                'href' => route('dashboard.account'),
            ];
        }

        return [
            'title' => 'Dashboard',
            'href' => route('dashboard'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function usersList(int $limit = 48): array
    {
        return User::query()
            ->withCount([
                'events',
                'events as unpaid_events_count' => fn ($query) => $query->where('is_paid', false),
                'events as locked_events_count' => fn ($query) => $query->where('status', Event::STATUS_LOCKED),
            ])
            ->withSum('events as total_storage_limit_bytes', 'storage_limit_bytes')
            ->withSum('events as total_storage_used_bytes', 'storage_used_bytes')
            ->with([
                'events' => fn ($query) => $query
                    ->select(['id', 'user_id', 'name', 'plan_id', 'created_at'])
                    ->with('plan:id,name')
                    ->latest('id')
                    ->limit(1),
            ])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (User $user): array {
                $latestEvent = $user->events->first();
                $storageQuota = $this->storageQuotaMeta(
                    (int) ($user->total_storage_limit_bytes ?? 0),
                    (int) ($user->total_storage_used_bytes ?? 0),
                );

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'isSuperAdmin' => $user->canAccessAdmin(),
                    'eventCount' => (int) ($user->events_count ?? 0),
                    'unpaidEventCount' => (int) ($user->unpaid_events_count ?? 0),
                    'lockedEventCount' => (int) ($user->locked_events_count ?? 0),
                    'latestEventName' => $latestEvent?->name,
                    'latestEventPlan' => $latestEvent?->plan?->name,
                    'latestEventUrl' => $latestEvent !== null ? route('events.show', $latestEvent) : null,
                    'createdAt' => $user->created_at?->toIso8601String(),
                    'storage' => $storageQuota,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function eventRows(int $limit = 48): array
    {
        return Event::query()
            ->with(['user:id,name,email', 'plan:id,name,currency,price_cents'])
            ->withCount(['assets', 'guests'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Event $event): array {
                [$statusLabel, $statusTone] = $this->eventStatusMeta($event);
                [$billingLabel, $billingTone] = $this->billingMeta($event);
                $storageQuota = $this->storageQuotaMeta(
                    (int) $event->storage_limit_bytes,
                    (int) $event->storage_used_bytes,
                );

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'ownerName' => $event->user?->name ?? 'Unknown owner',
                    'ownerEmail' => $event->user?->email ?? 'Unknown owner',
                    'planName' => $event->plan?->name ?? 'Custom plan',
                    'planPriceLabel' => $this->planPriceLabel($event->plan),
                    'statusLabel' => $statusLabel,
                    'statusTone' => $statusTone,
                    'billingLabel' => $billingLabel,
                    'billingTone' => $billingTone,
                    'eventDate' => $event->event_date?->toDateString(),
                    'createdAt' => $event->created_at?->toIso8601String(),
                    'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
                    'paidAt' => $event->paid_at?->toIso8601String(),
                    'guestCount' => (int) ($event->guests_count ?? 0),
                    'assetCount' => (int) ($event->assets_count ?? 0),
                    'isPaid' => $event->is_paid,
                    'storage' => $storageQuota,
                    'billingNote' => $event->billing_note,
                    'links' => [
                        'event' => route('events.show', $event),
                        'media' => route('events.media', $event),
                        'settings' => route('events.settings', $event),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function billingAttentionRows(int $limit = 24): array
    {
        return Event::query()
            ->with(['user:id,name,email', 'plan:id,name,currency,price_cents'])
            ->withCount('assets')
            ->where(function ($query): void {
                $query
                    ->where('status', Event::STATUS_LOCKED)
                    ->orWhere('status', Event::STATUS_EXPIRED)
                    ->orWhere('is_paid', false);
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Event::STATUS_LOCKED])
            ->orderBy('payment_due_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Event $event): array {
                [$queueLabel, $queueTone] = $this->billingQueueMeta($event);
                $storageQuota = $this->storageQuotaMeta(
                    (int) $event->storage_limit_bytes,
                    (int) $event->storage_used_bytes,
                );
                $cleanupPolicy = $this->cleanupPolicyMeta($event, $storageQuota);

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'ownerName' => $event->user?->name ?? 'Unknown owner',
                    'ownerEmail' => $event->user?->email ?? 'Unknown owner',
                    'planName' => $event->plan?->name ?? 'Custom plan',
                    'planPriceLabel' => $this->planPriceLabel($event->plan),
                    'queueLabel' => $queueLabel,
                    'queueTone' => $queueTone,
                    'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
                    'paidAt' => $event->paid_at?->toIso8601String(),
                    'status' => $event->status,
                    'assetCount' => (int) ($event->assets_count ?? 0),
                    'hasExportArchive' => $this->eventHasStoredExportArchive($event),
                    'canPurgeMedia' => $cleanupPolicy['canRunCleanup'],
                    'billingNote' => $event->billing_note,
                    'storage' => $storageQuota,
                    'cleanup' => $cleanupPolicy,
                    'links' => [
                        'event' => route('events.show', $event),
                        'settings' => route('events.settings', $event),
                        'cleanup' => route('admin.events.cleanup', $event),
                        'cleanupReview' => route('admin.events.cleanup-review', $event),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentPaymentRows(int $limit = 24): array
    {
        return Event::query()
            ->with(['user:id,name,email', 'plan:id,name,currency,price_cents'])
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->limit($limit)
            ->get()
            ->map(function (Event $event): array {
                $storageQuota = $this->storageQuotaMeta(
                    (int) $event->storage_limit_bytes,
                    (int) $event->storage_used_bytes,
                );

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'ownerName' => $event->user?->name ?? 'Unknown owner',
                    'ownerEmail' => $event->user?->email ?? 'Unknown owner',
                    'planName' => $event->plan?->name ?? 'Custom plan',
                    'planPriceLabel' => $this->planPriceLabel($event->plan),
                    'paidAt' => $event->paid_at?->toIso8601String(),
                    'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
                    'storage' => $storageQuota,
                    'links' => [
                        'event' => route('events.show', $event),
                        'settings' => route('events.settings', $event),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function planRows(): array
    {
        return Plan::query()
            ->withCount('events')
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('currency')
            ->orderBy('price_cents')
            ->get()
            ->map(function (Plan $plan): array {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'currency' => $plan->currency,
                    'priceCents' => (int) $plan->price_cents,
                    'priceLabel' => $this->planPriceLabel($plan),
                    'storageLimitBytes' => (int) $plan->storage_limit_bytes,
                    'storageLimitLabel' => $this->humanBytes((int) $plan->storage_limit_bytes),
                    'uploadLimit' => (int) $plan->upload_limit,
                    'retentionDays' => (int) $plan->retention_days,
                    'graceDays' => (int) $plan->grace_days,
                    'uploadWindowDays' => (int) $plan->upload_window_days,
                    'customizationTier' => (string) $plan->customization_tier,
                    'downloadAllEnabled' => (bool) $plan->download_all_enabled,
                    'moderationToolsEnabled' => (bool) $plan->moderation_tools_enabled,
                    'removeAppBranding' => (bool) $plan->remove_app_branding,
                    'videoMaxDurationSeconds' => (int) $plan->video_max_duration_seconds,
                    'photoMaxSizeBytes' => (int) $plan->photo_max_size_bytes,
                    'videoMaxSizeBytes' => (int) $plan->video_max_size_bytes,
                    'isActive' => $plan->is_active,
                    'isDefault' => $plan->is_default,
                    'eventCount' => (int) ($plan->events_count ?? 0),
                    'createdAt' => $plan->created_at?->toIso8601String(),
                    'updatedAt' => $plan->updated_at?->toIso8601String(),
                    'links' => [
                        'update' => route('admin.plans.update', $plan),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function cleanupRows(int $limit = 64): array
    {
        return Event::query()
            ->with(['user:id,name,email', 'plan:id,name,currency,price_cents'])
            ->withCount('assets')
            ->where(function ($query): void {
                $query
                    ->whereIn('status', [Event::STATUS_LOCKED, Event::STATUS_EXPIRED])
                    ->orWhereNotNull('cleanup_review_state');
            })
            ->latest('cleanup_reviewed_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Event $event): array {
                [$queueLabel, $queueTone] = $this->billingQueueMeta($event);
                $storageQuota = $this->storageQuotaMeta(
                    (int) $event->storage_limit_bytes,
                    (int) $event->storage_used_bytes,
                );
                $cleanupPolicy = $this->cleanupPolicyMeta($event, $storageQuota);

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'ownerName' => $event->user?->name ?? 'Unknown owner',
                    'ownerEmail' => $event->user?->email ?? 'Unknown owner',
                    'planName' => $event->plan?->name ?? 'Custom plan',
                    'planPriceLabel' => $this->planPriceLabel($event->plan),
                    'queueLabel' => $queueLabel,
                    'queueTone' => $queueTone,
                    'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
                    'paidAt' => $event->paid_at?->toIso8601String(),
                    'status' => $event->status,
                    'reviewStateRaw' => $event->cleanup_review_state,
                    'reviewedAt' => $event->cleanup_reviewed_at?->toIso8601String(),
                    'assetCount' => (int) ($event->assets_count ?? 0),
                    'hasExportArchive' => $this->eventHasStoredExportArchive($event),
                    'canPurgeMedia' => $cleanupPolicy['canRunCleanup'],
                    'billingNote' => $event->billing_note,
                    'storage' => $storageQuota,
                    'cleanup' => $cleanupPolicy,
                    'links' => [
                        'event' => route('events.show', $event),
                        'settings' => route('events.settings', $event),
                        'cleanup' => route('admin.events.cleanup', $event),
                        'cleanupReview' => route('admin.events.cleanup-review', $event),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function eventStatusMeta(Event $event): array
    {
        if ($event->onboarding_completed_at === null) {
            return ['Setup in progress', 'amber'];
        }

        return match ($event->status) {
            Event::STATUS_LIVE => ['Live now', 'emerald'],
            Event::STATUS_GRACE => ['Grace period', 'sky'],
            Event::STATUS_LOCKED => ['Locked', 'rose'],
            Event::STATUS_EXPIRED => ['Expired', 'zinc'],
            Event::STATUS_DRAFT => ['Draft', 'amber'],
            default => ['Scheduled', 'violet'],
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function billingMeta(Event $event): array
    {
        if ($event->is_paid) {
            return ['Paid', 'emerald'];
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;
        if ($paymentDueAt === null) {
            return ['Billing pending', 'amber'];
        }

        if ($paymentDueAt->isPast()) {
            return ['Payment overdue', 'rose'];
        }

        return ['Payment due soon', 'amber'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function billingQueueMeta(Event $event): array
    {
        if ($event->status === Event::STATUS_LOCKED) {
            return ['Locked', 'rose'];
        }

        if ($event->status === Event::STATUS_EXPIRED) {
            return ['Expired', 'zinc'];
        }

        if ($event->is_paid) {
            return ['Paid', 'emerald'];
        }

        if ($event->payment_due_at?->isPast()) {
            return ['Overdue', 'rose'];
        }

        return ['Awaiting payment', 'amber'];
    }

    private function planPriceLabel(?Plan $plan): string
    {
        if ($plan === null) {
            return 'Custom pricing';
        }

        return sprintf('%s %.2f', strtoupper($plan->currency), $plan->price_cents / 100);
    }

    /**
     * @return array<string, mixed>
     */
    private function planPayload(UpsertPlanRequest $request): array
    {
        $validated = $request->validated();

        return [
            'name' => trim((string) $validated['name']),
            'slug' => trim((string) $validated['slug']),
            'description' => filled($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
            'currency' => mb_strtoupper((string) $validated['currency']),
            'price_cents' => (int) $validated['price_cents'],
            'storage_limit_bytes' => (int) $validated['storage_limit_gb'] * 1073741824,
            'upload_limit' => (int) $validated['upload_limit'],
            'retention_days' => (int) $validated['retention_days'],
            'grace_days' => (int) $validated['grace_days'],
            'upload_window_days' => (int) $validated['upload_window_days'],
            'customization_tier' => (string) $validated['customization_tier'],
            'video_max_duration_seconds' => (int) $validated['video_max_duration_seconds'],
            'photo_max_size_bytes' => (int) $validated['photo_max_size_mb'] * 1048576,
            'video_max_size_bytes' => (int) $validated['video_max_size_mb'] * 1048576,
            'download_all_enabled' => (bool) ($validated['download_all_enabled'] ?? false),
            'moderation_tools_enabled' => (bool) ($validated['moderation_tools_enabled'] ?? false),
            'remove_app_branding' => (bool) ($validated['remove_app_branding'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ];
    }

    private function syncDefaultPlanFlag(Plan $plan, bool $shouldBeDefault): void
    {
        if (! $shouldBeDefault) {
            return;
        }

        Plan::query()
            ->where('currency', $plan->currency)
            ->whereKeyNot($plan->id)
            ->update(['is_default' => false]);

        if (! $plan->is_default) {
            $plan->forceFill([
                'is_default' => true,
                'is_active' => true,
            ])->save();
        }
    }

    private function handleClearExportCleanup(Event $event): RedirectResponse
    {
        $cleared = $this->clearEventExportArchive($event);

        return back()->with(
            $cleared ? 'success' : 'info',
            $cleared ? 'Stored export archive cleared.' : 'No stored export archive was found for this event.',
        );
    }

    private function handlePurgeMediaCleanup(Event $event): RedirectResponse
    {
        $cleanupPolicy = $this->cleanupPolicyMeta(
            $event,
            $this->storageQuotaMeta((int) $event->storage_limit_bytes, (int) $event->storage_used_bytes),
        );

        if (! $cleanupPolicy['canRunCleanup']) {
            throw ValidationException::withMessages([
                'action' => 'Media purge is only available after cleanup review is approved for locked or expired events.',
            ]);
        }

        $result = $this->purgeEventMedia($event);

        if ($result['deletedAssetCount'] === 0 && ! $result['clearedExport']) {
            return back()->with('info', 'This event had no stored media or export archive to purge.');
        }

        $message = "{$result['deletedAssetCount']} media item(s) purged";
        if ($result['reclaimedStorageBytes'] > 0) {
            $message .= " and {$this->humanBytes($result['reclaimedStorageBytes'])} reclaimed";
        }
        if ($result['clearedExport']) {
            $message .= '. Export archive cleared too.';
        } else {
            $message .= '.';
        }

        return back()->with('success', $message);
    }

    private function canPurgeEventMedia(Event $event): bool
    {
        return in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true);
    }

    private function eventHasStoredExportArchive(Event $event): bool
    {
        return is_string($event->media_export_disk)
            && $event->media_export_disk !== ''
            && is_string($event->media_export_path)
            && $event->media_export_path !== '';
    }

    /**
     * @param  array{
     *   limitBytes: int,
     *   usedBytes: int,
     *   freeBytes: int,
     *   usagePercent: int,
     *   isNearLimit: bool,
     *   isOverLimit: bool
     * }  $storageQuota
     * @return array{
     *   stateCode: string,
     *   stateLabel: string,
     *   stateTone: string,
     *   hint: string,
     *   candidateAt: string|null,
     *   canRunCleanup: bool
     * }
     */
    private function cleanupPolicyMeta(Event $event, array $storageQuota): array
    {
        $referenceAt = match ($event->status) {
            Event::STATUS_LOCKED => $event->payment_due_at ?? $event->grace_ends_at ?? $event->hard_lock_at,
            Event::STATUS_EXPIRED => $event->retention_ends_at,
            default => null,
        };

        $thresholdDays = $event->status === Event::STATUS_EXPIRED
            ? (int) config('events.cleanup_policy.expired_candidate_after_days', 7)
            : (int) config('events.cleanup_policy.locked_candidate_after_days', 14);
        $candidateAt = $referenceAt?->copy()->addDays(max(0, $thresholdDays));
        $needsCleanup = $storageQuota['usedBytes'] > 0 || $this->eventHasStoredExportArchive($event);
        $isDue = $candidateAt !== null && now($event->timezone ?: config('events.default_timezone', 'UTC'))->gte($candidateAt);

        if ($event->cleanup_review_state === 'completed') {
            return [
                'stateCode' => 'completed',
                'stateLabel' => 'Completed',
                'stateTone' => 'emerald',
                'hint' => 'Cleanup already ran for this event.',
                'candidateAt' => $candidateAt?->toIso8601String(),
                'canRunCleanup' => false,
            ];
        }

        if ($event->cleanup_review_state === 'protected') {
            return [
                'stateCode' => 'protected',
                'stateLabel' => 'Protected',
                'stateTone' => 'sky',
                'hint' => 'Cleanup is intentionally blocked until you clear protection.',
                'candidateAt' => $candidateAt?->toIso8601String(),
                'canRunCleanup' => false,
            ];
        }

        if (! $this->canPurgeEventMedia($event) || ! $needsCleanup) {
            return [
                'stateCode' => 'not_due',
                'stateLabel' => 'Not due',
                'stateTone' => 'zinc',
                'hint' => 'Cleanup only applies to locked or expired events that still occupy storage.',
                'candidateAt' => $candidateAt?->toIso8601String(),
                'canRunCleanup' => false,
            ];
        }

        if (! $isDue) {
            return [
                'stateCode' => 'cooldown',
                'stateLabel' => 'Cooldown',
                'stateTone' => 'zinc',
                'hint' => 'This event is not old enough to become a cleanup candidate yet.',
                'candidateAt' => $candidateAt?->toIso8601String(),
                'canRunCleanup' => false,
            ];
        }

        if ($event->cleanup_review_state === 'approved') {
            return [
                'stateCode' => 'approved',
                'stateLabel' => 'Approved',
                'stateTone' => 'emerald',
                'hint' => 'Cleanup has been approved and purge can run safely.',
                'candidateAt' => $candidateAt?->toIso8601String(),
                'canRunCleanup' => true,
            ];
        }

        return [
            'stateCode' => 'review',
            'stateLabel' => 'Needs review',
            'stateTone' => 'amber',
            'hint' => 'Review and explicitly approve this event before destructive cleanup runs.',
            'candidateAt' => $candidateAt?->toIso8601String(),
            'canRunCleanup' => false,
        ];
    }

    private function clearEventExportArchive(Event $event): bool
    {
        return DB::transaction(function () use ($event): bool {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $hadArchive = $this->eventHasStoredExportArchive($lockedEvent);

            if ($hadArchive) {
                Storage::disk((string) $lockedEvent->media_export_disk)->delete((string) $lockedEvent->media_export_path);
            }

            $this->resetEventExportState($lockedEvent);

            return $hadArchive;
        });
    }

    /**
     * @return array{deletedAssetCount: int, reclaimedStorageBytes: int, clearedExport: bool}
     */
    private function purgeEventMedia(Event $event): array
    {
        return DB::transaction(function () use ($event): array {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $assets = EventAsset::query()
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->get();

            $deletedAssetCount = $assets->count();
            $reclaimedStorageBytes = (int) $assets->sum('size_bytes');

            foreach ($assets as $asset) {
                $this->deleteEventAssetFiles($asset);
                $asset->delete();
            }

            $clearedExport = $this->eventHasStoredExportArchive($lockedEvent);
            $this->resetEventExportState($lockedEvent, deleteStoredFile: $clearedExport);

            $lockedEvent->forceFill([
                'storage_used_bytes' => max(0, (int) $lockedEvent->storage_used_bytes - $reclaimedStorageBytes),
                'upload_count' => max(0, (int) $lockedEvent->upload_count - $deletedAssetCount),
                'cleanup_review_state' => 'completed',
                'cleanup_reviewed_at' => now(),
            ])->save();

            return [
                'deletedAssetCount' => $deletedAssetCount,
                'reclaimedStorageBytes' => $reclaimedStorageBytes,
                'clearedExport' => $clearedExport,
            ];
        });
    }

    private function deleteEventAssetFiles(EventAsset $asset): void
    {
        Storage::disk($asset->disk)->delete(array_values(array_filter([
            $asset->path,
            $asset->thumbnail_path,
            $asset->preview_path,
            $asset->watermarked_thumbnail_path,
            $asset->watermarked_preview_path,
            $asset->watermarked_download_path,
            $asset->video_thumbnail_path,
            $asset->watermarked_video_thumbnail_path,
            $asset->video_preview_path,
            $asset->watermarked_video_preview_path,
            $asset->watermarked_video_download_path,
        ])));
    }

    private function resetEventExportState(Event $event, bool $deleteStoredFile = false): void
    {
        if ($deleteStoredFile && $this->eventHasStoredExportArchive($event)) {
            Storage::disk((string) $event->media_export_disk)->delete((string) $event->media_export_path);
        }

        $event->forceFill([
            'media_export_status' => null,
            'media_export_token' => null,
            'media_export_disk' => null,
            'media_export_path' => null,
            'media_export_requested_at' => null,
            'media_export_started_at' => null,
            'media_export_completed_at' => null,
            'media_export_failed_at' => null,
            'media_export_error' => null,
        ])->save();
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return sprintf('%s %s', $value >= 10 || $power === 0 ? number_format($value, 0) : number_format($value, 1), $units[$power]);
    }

    /**
     * @return array{
     *   limitBytes: int,
     *   usedBytes: int,
     *   freeBytes: int,
     *   usagePercent: int,
     *   isNearLimit: bool,
     *   isOverLimit: bool
     * }
     */
    private function storageQuotaMeta(int $limitBytes, int $usedBytes): array
    {
        $safeLimit = max(0, $limitBytes);
        $safeUsed = max(0, $usedBytes);
        $freeBytes = max(0, $safeLimit - $safeUsed);
        $usagePercent = $safeLimit > 0 ? (int) round(($safeUsed / $safeLimit) * 100) : 0;

        return [
            'limitBytes' => $safeLimit,
            'usedBytes' => $safeUsed,
            'freeBytes' => $freeBytes,
            'usagePercent' => max(0, $usagePercent),
            'isNearLimit' => $safeLimit > 0 && $safeUsed >= (int) floor($safeLimit * 0.8),
            'isOverLimit' => $safeLimit > 0 && $safeUsed > $safeLimit,
        ];
    }
}
