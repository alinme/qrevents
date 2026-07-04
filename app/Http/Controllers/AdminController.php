<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharesAdminNavigation;
use App\Http\Requests\UpsertPlanRequest;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    use SharesAdminNavigation;

    public function index(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Overview', [
            ...$this->adminPanelProps(),
            'summary' => $this->summary(),
            'recentEvents' => $this->eventRows(
                Event::query()
                    ->with(['user:id,name,email', 'plan:id,name,currency,price_cents'])
                    ->withCount('assets')
                    ->latest('id')
                    ->limit(8)
                    ->get(),
            ),
        ]);
    }

    public function events(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $validStatuses = [
            Event::STATUS_DRAFT, Event::STATUS_SCHEDULED, Event::STATUS_LIVE,
            Event::STATUS_GRACE, Event::STATUS_LOCKED, Event::STATUS_EXPIRED,
        ];
        $status = in_array($status, $validStatuses, true) ? $status : '';

        $events = Event::query()
            ->with(['user:id,name,email', 'plan:id,name,currency,price_cents'])
            ->withCount('assets')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Events', [
            ...$this->adminPanelProps(),
            'events' => $this->eventRows($events->getCollection()),
            'pagination' => [
                'currentPage' => $events->currentPage(),
                'lastPage' => $events->lastPage(),
                'total' => $events->total(),
                'prevPageUrl' => $events->previousPageUrl(),
                'nextPageUrl' => $events->nextPageUrl(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => Event::STATUS_LIVE, 'label' => __('admin.status.live')],
                ['value' => Event::STATUS_SCHEDULED, 'label' => __('admin.status.scheduled')],
                ['value' => Event::STATUS_GRACE, 'label' => __('admin.status.grace')],
                ['value' => Event::STATUS_LOCKED, 'label' => __('admin.status.locked')],
                ['value' => Event::STATUS_EXPIRED, 'label' => __('admin.status.expired')],
                ['value' => Event::STATUS_DRAFT, 'label' => __('admin.status.draft')],
            ],
        ]);
    }

    public function plans(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return Inertia::render('admin/Plans', [
            ...$this->adminPanelProps(),
            'plans' => $this->planRows(),
            'planStoreUrl' => route('admin.plans.store'),
        ]);
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

    /**
     * @return array<string, mixed>
     */
    private function summary(): array
    {
        $statusCounts = Event::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $storageUsedBytes = (int) (Event::query()->sum('storage_used_bytes') ?? 0);
        $totalEvents = Event::query()->count();
        $paidEvents = Event::query()->where('is_paid', true)->count();

        return [
            'totalUsers' => User::query()->count(),
            'totalEvents' => $totalEvents,
            'totalUploads' => EventAsset::query()->count(),
            'storageUsedBytes' => $storageUsedBytes,
            'storageUsedLabel' => $this->humanBytes($storageUsedBytes),
            'businessAccounts' => User::query()->where('account_type', User::ACCOUNT_TYPE_BUSINESS)->count(),
            'paidEvents' => $paidEvents,
            'unpaidEvents' => max(0, $totalEvents - $paidEvents),
            'newUsers7d' => User::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'eventsByStatus' => collect([
                Event::STATUS_DRAFT => [__('admin.status.draft'), 'amber'],
                Event::STATUS_SCHEDULED => [__('admin.status.scheduled'), 'violet'],
                Event::STATUS_LIVE => [__('admin.status.live'), 'emerald'],
                Event::STATUS_GRACE => [__('admin.status.grace'), 'sky'],
                Event::STATUS_LOCKED => [__('admin.status.locked'), 'rose'],
                Event::STATUS_EXPIRED => [__('admin.status.expired'), 'zinc'],
            ])
                ->map(fn (array $meta, string $status): array => [
                    'status' => $status,
                    'label' => $meta[0],
                    'tone' => $meta[1],
                    'count' => (int) ($statusCounts[$status] ?? 0),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return list<array<string, mixed>>
     */
    private function eventRows($events): array
    {
        return $events
            ->map(function (Event $event): array {
                [$statusLabel, $statusTone] = $this->eventStatusMeta($event);
                [$billingLabel, $billingTone] = $this->billingMeta($event);

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
                    'isPaid' => (bool) $event->is_paid,
                    'status' => (string) $event->status,
                    'isSuspended' => $event->status === Event::STATUS_LOCKED,
                    'assetCount' => (int) ($event->assets_count ?? 0),
                    'createdAt' => $event->created_at?->toIso8601String(),
                    'links' => [
                        'settings' => route('events.settings', $event),
                        'suspend' => route('admin.events.suspend', $event),
                        'extend' => route('admin.events.extend', $event),
                        'destroy' => route('admin.events.destroy', $event),
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
            ->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'currency' => $plan->currency,
                'priceCents' => (int) $plan->price_cents,
                'priceLabel' => $this->planPriceLabel($plan),
                'storageLimitGb' => (int) round($plan->storage_limit_bytes / 1073741824),
                'storageLimitLabel' => $this->humanBytes((int) $plan->storage_limit_bytes),
                'uploadLimit' => (int) $plan->upload_limit,
                'retentionDays' => (int) $plan->retention_days,
                'graceDays' => (int) $plan->grace_days,
                'uploadWindowDays' => (int) $plan->upload_window_days,
                'customizationTier' => (string) $plan->customization_tier,
                'videoMaxDurationSeconds' => (int) $plan->video_max_duration_seconds,
                'photoMaxSizeMb' => (int) round($plan->photo_max_size_bytes / 1048576),
                'videoMaxSizeMb' => (int) round($plan->video_max_size_bytes / 1048576),
                'downloadAllEnabled' => (bool) $plan->download_all_enabled,
                'moderationToolsEnabled' => (bool) $plan->moderation_tools_enabled,
                'removeAppBranding' => (bool) $plan->remove_app_branding,
                'isActive' => (bool) $plan->is_active,
                'isDefault' => (bool) $plan->is_default,
                'eventCount' => (int) ($plan->events_count ?? 0),
                'links' => [
                    'update' => route('admin.plans.update', $plan),
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function eventStatusMeta(Event $event): array
    {
        if ($event->onboarding_completed_at === null) {
            return [__('admin.status.setup_in_progress'), 'amber'];
        }

        return match ($event->status) {
            Event::STATUS_LIVE => [__('admin.status.live_now'), 'emerald'],
            Event::STATUS_GRACE => [__('admin.status.grace'), 'sky'],
            Event::STATUS_LOCKED => [__('admin.status.locked'), 'rose'],
            Event::STATUS_EXPIRED => [__('admin.status.expired'), 'zinc'],
            Event::STATUS_DRAFT => [__('admin.status.draft'), 'amber'],
            default => [__('admin.status.scheduled'), 'violet'],
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function billingMeta(Event $event): array
    {
        if ($event->is_paid) {
            return [__('admin.events.billing.paid'), 'emerald'];
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;
        if ($paymentDueAt === null) {
            return ['Billing pending', 'amber'];
        }

        if ($paymentDueAt->isPast()) {
            return [__('admin.events.billing.overdue'), 'rose'];
        }

        return [__('admin.events.billing.due_soon'), 'amber'];
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
}
