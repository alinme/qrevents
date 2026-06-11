<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventCollaborator;
use App\Support\AuthOnboardingRedirector;
use App\Support\IsgdShortUrlManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const BUSINESS_STATUS_ALL = 'all';

    private const BUSINESS_STATUS_ATTENTION = 'attention';

    private const BUSINESS_STATUS_UNPAID = 'unpaid';

    private const BUSINESS_STATUS_OVERDUE = 'overdue';

    private const BUSINESS_STATUS_LIVE = 'live';

    private const BUSINESS_STATUS_EXPORT_READY = 'export_ready';

    private const BUSINESS_SELECTION_SCOPE_NONE = 'none';

    private const BUSINESS_SELECTION_SCOPE_ALL_FILTERED = 'all_filtered';

    public function index(Request $request): Response|RedirectResponse
    {
        $onboardingRedirect = app(AuthOnboardingRedirector::class)->dashboardRedirect($request->user());
        if ($onboardingRedirect !== null) {
            return $onboardingRedirect;
        }

        return $this->account($request);
    }

    public function account(Request $request): Response|RedirectResponse
    {
        $onboardingRedirect = app(AuthOnboardingRedirector::class)->dashboardRedirect($request->user());
        if ($onboardingRedirect !== null) {
            return $onboardingRedirect;
        }

        $data = $this->accountData($request);

        return Inertia::render('Dashboard', [
            'summary' => $data['summary'],
            'continueSetupEvent' => $data['continueSetupEvent'],
            'dashboardLinks' => $data['dashboardLinks'],
            'ownedEvents' => $data['ownedEvents'],
            'collaboratorEvents' => $data['collaboratorEvents'],
            'recentActivity' => $data['recentActivity'],
            'sidebarLabel' => 'Events',
            'showDashboardModal' => $request->session()->pull('show_dashboard_modal', false),
        ]);
    }

    public function ownedEvents(Request $request): RedirectResponse
    {
        return redirect()->to($this->accountOverviewUrl($request).'#events');
    }

    public function recentActivity(Request $request): RedirectResponse
    {
        return redirect()->to($this->accountOverviewUrl($request).'#activity');
    }

    /**
     * @return array<string, mixed>
     */
    private function accountData(Request $request): array
    {
        $accountOverviewUrl = $this->accountOverviewUrl($request);

        $ownedEvents = $request->user()
            ->events()
            ->with('plan')
            ->withCount('guests')
            ->latest('event_date')
            ->latest('id')
            ->get();

        $collaborations = $request->user()
            ->eventCollaborations()
            ->whereIn('status', ['active', 'accepted'])
            ->with([
                'event' => fn ($query) => $query
                    ->with('plan')
                    ->withCount('guests'),
            ])
            ->latest('accepted_at')
            ->latest('id')
            ->get()
            ->filter(fn (EventCollaborator $collaboration): bool => $collaboration->event !== null)
            ->values();

        $accessibleEvents = $ownedEvents
            ->concat($collaborations->pluck('event'))
            ->unique('id')
            ->values();
        $eventIds = $accessibleEvents->pluck('id')->all();
        $assetStats = $this->assetStatsByEvent($eventIds);
        $defaultStats = $this->defaultAssetStats();

        $ownedEventCards = $ownedEvents
            ->map(fn (Event $event): array => $this->dashboardEventCard(
                $event,
                'owner',
                null,
                $assetStats[$event->id] ?? $defaultStats,
            ))
            ->values();

        $collaboratorEventCards = $collaborations
            ->map(fn (EventCollaborator $collaboration): array => $this->dashboardEventCard(
                $collaboration->event,
                'collaborator',
                $collaboration,
                $assetStats[$collaboration->event->id] ?? $defaultStats,
            ))
            ->values();

        $continueSetupEvent = $ownedEvents
            ->first(fn (Event $event): bool => $event->onboarding_completed_at === null);
        $manageableEvent = $ownedEvents->first()
            ?? $collaborations->first(
                fn (EventCollaborator $collaboration): bool => $collaboration->role === 'manager',
            )?->event;
        $recentActivity = $this->recentActivityItems($eventIds);

        return [
            'summary' => [
                'ownedEventCount' => $ownedEvents->count(),
                'collaboratorEventCount' => $collaboratorEventCards->count(),
                'pendingSetupCount' => $ownedEvents
                    ->filter(fn (Event $event): bool => $event->onboarding_completed_at === null)
                    ->count(),
                'totalUploadCount' => $ownedEventCards
                    ->concat($collaboratorEventCards)
                    ->sum('assetCount'),
                'pendingModerationCount' => $ownedEventCards
                    ->concat($collaboratorEventCards)
                    ->sum('processingCount'),
                'readyExportCount' => $ownedEventCards
                    ->concat($collaboratorEventCards)
                    ->filter(fn (array $card): bool => $card['canManage'] && $card['mediaExportStatus'] === 'ready')
                    ->count(),
            ],
            'continueSetupEvent' => $continueSetupEvent !== null
                ? $this->dashboardEventCard(
                    $continueSetupEvent,
                    'owner',
                    null,
                    $assetStats[$continueSetupEvent->id] ?? $defaultStats,
                )
                : null,
            'dashboardLinks' => [
                'overview' => $accountOverviewUrl,
                'ownedEvents' => $accountOverviewUrl.'#events',
                'recentActivity' => $accountOverviewUrl.'#activity',
            ],
            'ownedEvents' => $ownedEventCards->all(),
            'collaboratorEvents' => $collaboratorEventCards->all(),
            'recentActivity' => $recentActivity,
        ];
    }

    private function accountOverviewUrl(Request $request): string
    {
        return route('dashboard');
    }

    /**
     * @param  list<int>  $eventIds
     * @return array<int, array{
     *   asset_count: int,
     *   approved_count: int,
     *   processing_count: int,
     *   rejected_count: int,
     *   last_upload_at: string|null
     * }>
     */
    private function assetStatsByEvent(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        return EventAsset::query()
            ->selectRaw('event_id')
            ->selectRaw('COUNT(*) as asset_count')
            ->selectRaw("SUM(CASE WHEN moderation_status = 'approved' THEN 1 ELSE 0 END) as approved_count")
            ->selectRaw("SUM(CASE WHEN moderation_status = 'processing' THEN 1 ELSE 0 END) as processing_count")
            ->selectRaw("SUM(CASE WHEN moderation_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count")
            ->selectRaw('MAX(created_at) as last_upload_at')
            ->whereIn('event_id', $eventIds)
            ->groupBy('event_id')
            ->get()
            ->mapWithKeys(
                fn (EventAsset $asset): array => [
                    $asset->event_id => [
                        'asset_count' => (int) $asset->asset_count,
                        'approved_count' => (int) $asset->approved_count,
                        'processing_count' => (int) $asset->processing_count,
                        'rejected_count' => (int) $asset->rejected_count,
                        'last_upload_at' => $asset->last_upload_at,
                    ],
                ],
            )
            ->all();
    }

    /**
     * @return array{
     *   asset_count: int,
     *   approved_count: int,
     *   processing_count: int,
     *   rejected_count: int,
     *   last_upload_at: string|null
     * }
     */
    private function defaultAssetStats(): array
    {
        return [
            'asset_count' => 0,
            'approved_count' => 0,
            'processing_count' => 0,
            'rejected_count' => 0,
            'last_upload_at' => null,
        ];
    }

    /**
     * @param  array{
     *   asset_count: int,
     *   approved_count: int,
     *   processing_count: int,
     *   rejected_count: int,
     *   last_upload_at: string|null
     * }  $assetStats
     * @return array<string, mixed>
     */
    private function dashboardEventCard(
        Event $event,
        string $context,
        ?EventCollaborator $collaboration,
        array $assetStats,
    ): array {
        $roleMeta = $this->eventRoleMeta($context, $collaboration);
        $statusMeta = $this->eventStatusMeta($event);
        $billingMeta = $this->billingMeta($event);
        $mediaExportMeta = $this->mediaExportMeta($event);
        [$planSlug, $planTone] = $this->dashboardPlanMeta($event);
        $publicShortLinks = app(IsgdShortUrlManager::class)->forEvent($event);
        $planName = $event->plan?->name ?? __('dashboard.business.fallback.custom_plan');
        $primaryActionKey = $event->onboarding_completed_at === null ? 'continue_setup' : 'open_workspace';

        return [
            'id' => $event->id,
            'name' => $event->name,
            'plan' => $planName,
            'planDetails' => [
                'name' => $planName,
                'slug' => $planSlug,
                'tone' => $planTone,
                'priceCents' => (int) ($event->plan?->price_cents ?? 0),
                'storageLimitBytes' => (int) ($event->plan?->storage_limit_bytes ?? $event->storage_limit_bytes),
                'uploadLimit' => (int) ($event->plan?->upload_limit ?? $event->upload_limit),
                'uploadWindowDays' => (int) ($event->plan?->upload_window_days ?? $event->upload_window_days),
                'downloadAllEnabled' => (bool) $event->download_all_enabled,
                'moderationToolsEnabled' => (bool) $event->moderation_tools_enabled,
                'removeAppBranding' => (bool) $event->remove_app_branding,
            ],
            'eventDate' => $event->event_date?->toDateString(),
            'timezone' => $event->timezone,
            'roleKey' => $roleMeta['key'],
            'roleLabel' => $roleMeta['label'],
            'roleTone' => $roleMeta['tone'],
            'statusKey' => $statusMeta['key'],
            'statusLabel' => $statusMeta['label'],
            'statusTone' => $statusMeta['tone'],
            'billingKey' => $billingMeta['key'],
            'billingLabel' => $billingMeta['label'],
            'billingTone' => $billingMeta['tone'],
            'mediaExportStatus' => is_string($event->media_export_status) && $event->media_export_status !== ''
                ? $event->media_export_status
                : 'idle',
            'mediaExportKey' => $mediaExportMeta['key'],
            'mediaExportLabel' => $mediaExportMeta['label'],
            'mediaExportTone' => $mediaExportMeta['tone'],
            'uploadCount' => $event->upload_count,
            'uploadLimit' => $event->upload_limit,
            'storageUsedBytes' => $event->storage_used_bytes,
            'storageLimitBytes' => $event->storage_limit_bytes,
            'guestCount' => (int) ($event->guests_count ?? 0),
            'assetCount' => $assetStats['asset_count'],
            'approvedCount' => $assetStats['approved_count'],
            'processingCount' => $assetStats['processing_count'],
            'rejectedCount' => $assetStats['rejected_count'],
            'lastUploadAt' => $assetStats['last_upload_at'],
            'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
            'isPaid' => $event->is_paid,
            'onboardingComplete' => $event->onboarding_completed_at !== null,
            'primaryAction' => [
                'key' => $primaryActionKey,
                'label' => $this->primaryActionLabel($primaryActionKey),
                'url' => $event->onboarding_completed_at === null
                    ? $this->onboardingStepUrl($event)
                    : route('events.show', $event),
            ],
            'links' => [
                'dashboard' => route('events.show', $event),
                'media' => route('events.media', $event),
                'settings' => route('events.settings', $event),
                'billing' => route('events.settings', ['event' => $event, 'tab' => 'billing']),
                'album' => route('events.album', $event->publicAlbumCode()),
                'albumShortUrl' => $publicShortLinks['albumShortUrl'],
                'wall' => route('events.wall', $event->publicAlbumCode()),
                'wallShortUrl' => $publicShortLinks['wallShortUrl'],
                'albumAccessCode' => $event->publicAlbumCode(),
                'albumEntry' => route('events.album.access.show'),
                'albumEntryShortcut' => 'https://is.gd/evsmrt',
                'mediaExportDownload' => route('events.exports.media.download', $event),
            ],
            'canManage' => $context === 'owner' || $collaboration?->role === 'manager',
        ];
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function dashboardPlanMeta(Event $event): array
    {
        if (! is_string($event->plan?->slug) || $event->plan->slug === '') {
            return [null, 'dark'];
        }

        $planSlug = Str::lower($event->plan->slug);

        return match ($planSlug) {
            'free' => ['free', 'zinc'],
            'plus' => ['plus', 'sky'],
            'pro' => ['pro', 'emerald'],
            default => [$planSlug, 'dark'],
        };
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    private function eventRoleMeta(string $context, ?EventCollaborator $collaboration): array
    {
        if ($context === 'owner') {
            return [
                'key' => 'owner',
                'label' => __('dashboard.business.badges.role.owner'),
                'tone' => 'dark',
            ];
        }

        $roleKey = $collaboration?->role === 'manager' ? 'manager' : 'viewer';

        return [
            'key' => $roleKey,
            'label' => __("dashboard.business.badges.role.{$roleKey}"),
            'tone' => $roleKey === 'manager' ? 'sky' : 'zinc',
        ];
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    private function eventStatusMeta(Event $event): array
    {
        $statusKey = 'scheduled';
        $statusTone = 'violet';

        if ($event->onboarding_completed_at === null) {
            $statusKey = 'setup_in_progress';
            $statusTone = 'amber';
        } else {
            match ($event->status) {
                Event::STATUS_LIVE => [$statusKey, $statusTone] = ['live', 'emerald'],
                Event::STATUS_GRACE => [$statusKey, $statusTone] = ['grace', 'sky'],
                Event::STATUS_LOCKED => [$statusKey, $statusTone] = ['locked', 'rose'],
                Event::STATUS_EXPIRED => [$statusKey, $statusTone] = ['expired', 'zinc'],
                Event::STATUS_DRAFT => [$statusKey, $statusTone] = ['draft', 'amber'],
                default => [$statusKey, $statusTone] = ['scheduled', 'violet'],
            };
        }

        return [
            'key' => $statusKey,
            'label' => __("dashboard.business.badges.status.{$statusKey}"),
            'tone' => $statusTone,
        ];
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    private function billingMeta(Event $event): array
    {
        $billingKey = 'payment_due_soon';
        $billingTone = 'amber';

        if ($event->is_paid) {
            $billingKey = 'paid';
            $billingTone = 'emerald';
        } else {
            $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;

            if ($paymentDueAt === null) {
                $billingKey = 'billing_pending';
                $billingTone = 'amber';
            } elseif ($paymentDueAt->isPast()) {
                $billingKey = 'payment_overdue';
                $billingTone = 'rose';
            } else {
                $billingKey = 'payment_due_soon';
                $billingTone = 'amber';
            }
        }

        return [
            'key' => $billingKey,
            'label' => __("dashboard.business.badges.billing.{$billingKey}"),
            'tone' => $billingTone,
        ];
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    private function mediaExportMeta(Event $event): array
    {
        [$mediaExportKey, $mediaExportTone] = match ($event->media_export_status) {
            'ready' => ['ready', 'emerald'],
            'pending', 'processing' => ['running', 'sky'],
            'failed' => ['failed', 'rose'],
            default => ['idle', 'zinc'],
        };

        return [
            'key' => $mediaExportKey,
            'label' => __("dashboard.business.badges.media_export.{$mediaExportKey}"),
            'tone' => $mediaExportTone,
        ];
    }

    private function onboardingStepUrl(Event $event): string
    {
        return match ($event->onboarding_step) {
            'creating' => route('onboarding.creating', $event),
            'photos' => route('onboarding.photos', $event),
            default => route('onboarding.create'),
        };
    }

    /**
     * @param  list<int>  $eventIds
     * @return list<array<string, mixed>>
     */
    private function recentActivityItems(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        return EventAsset::query()
            ->with('event:id,name')
            ->whereIn('event_id', $eventIds)
            ->latest('id')
            ->limit(32)
            ->get()
            ->map(function (EventAsset $asset): array {
                $metadata = is_array($asset->metadata) ? $asset->metadata : [];

                return [
                    'id' => $asset->id,
                    'eventName' => $asset->event?->name ?? 'Event',
                    'eventUrl' => route('events.show', $asset->event_id),
                    'activityUrl' => route('events.media', [
                        'event' => $asset->event_id,
                        'asset' => $asset->id,
                    ]),
                    'kind' => $asset->kind,
                    'guestName' => is_string($metadata['guest_name'] ?? null) && trim((string) $metadata['guest_name']) !== ''
                        ? trim((string) $metadata['guest_name'])
                        : 'Guest',
                    'summary' => match ($asset->kind) {
                        'text' => is_string($metadata['text'] ?? null) && trim((string) $metadata['text']) !== ''
                            ? trim((string) $metadata['text'])
                            : 'Shared a text post',
                        default => is_string($metadata['message'] ?? null) && trim((string) $metadata['message']) !== ''
                            ? trim((string) $metadata['message'])
                            : 'Shared new media',
                    },
                    'moderationStatus' => $asset->moderation_status,
                    'createdAt' => $asset->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function primaryActionLabel(string $key): string
    {
        return __("dashboard.business.actions.primary.{$key}");
    }
}
