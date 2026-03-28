<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessDashboardRequest;
use App\Jobs\GenerateEventMediaExport;
use App\Models\BusinessWalletTransaction;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventCollaborator;
use App\Support\AuthOnboardingRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        if ($request->user()->canAccessAdmin()) {
            return to_route('admin.overview');
        }

        $onboardingRedirect = app(AuthOnboardingRedirector::class)->dashboardRedirect($request->user());
        if ($onboardingRedirect !== null) {
            return $onboardingRedirect;
        }

        if ($request->user()->canAccessBusinessDashboard()) {
            return to_route('dashboard.business');
        }

        $singleAccessibleEvent = $this->singleAccessibleEvent($request);
        if ($singleAccessibleEvent !== null) {
            if ($singleAccessibleEvent->user_id === $request->user()->id && $singleAccessibleEvent->onboarding_completed_at === null) {
                return redirect()->to($this->onboardingStepUrl($singleAccessibleEvent));
            }

            return to_route('events.show', $singleAccessibleEvent);
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
            'businessOverview' => $data['businessOverview'],
            'businessAttentionEvents' => $data['businessAttentionEvents'],
            'continueSetupEvent' => $data['continueSetupEvent'],
            'accountNavigation' => $data['accountNavigation'],
            'dashboardLinks' => $data['dashboardLinks'],
            'ownedEvents' => $data['ownedEvents'],
            'collaboratorEvents' => $data['collaboratorEvents'],
            'recentActivity' => $data['recentActivity'],
            'sidebarLabel' => $request->user()->canAccessBusinessDashboard() ? 'Portfolio' : 'Events',
            'showDashboardModal' => $request->session()->pull('show_dashboard_modal', false),
        ]);
    }

    public function business(BusinessDashboardRequest $request): Response
    {
        abort_unless($request->user()->canAccessBusinessDashboard(), 403);

        $data = $this->accountData($request);
        $filters = $this->businessFilters($request);
        $filteredAttentionEvents = $this->filterBusinessAttentionEvents(
            collect($data['businessAttentionEvents']),
            $filters,
        );
        $filteredOwnedEvents = $this->filterBusinessOwnedEvents(
            collect($data['ownedEvents']),
            $filters,
        );
        $ownedEventsPaginator = $this->paginateBusinessOwnedEvents(
            $filteredOwnedEvents,
            $request,
        );

        return Inertia::render('dashboard/BusinessOverview', [
            'summary' => $data['summary'],
            'businessOverview' => $data['businessOverview'],
            'walletActivity' => $data['walletActivity'],
            'businessAttentionEvents' => $filteredAttentionEvents->take(6)->all(),
            'businessAttentionSummary' => [
                'visibleCount' => $filteredAttentionEvents->count(),
                'totalCount' => count($data['businessAttentionEvents']),
            ],
            'filters' => $this->businessFilterMeta(
                collect($data['ownedEvents']),
                collect($data['businessAttentionEvents']),
                $filters,
            ),
            'quickActions' => $data['quickActions'],
            'accountNavigation' => $data['accountNavigation'],
            'dashboardLinks' => $data['dashboardLinks'],
            'businessActionLinks' => [
                'startExports' => route('dashboard.business.exports.start'),
                'billingQueueDownload' => route('dashboard.business.billing-queue'),
                'walletHistory' => route('dashboard.business.wallet.history'),
            ],
            'sidebarLabel' => 'Business',
            'ownedEvents' => $ownedEventsPaginator->items(),
            'ownedEventsPagination' => $this->paginationMeta($ownedEventsPaginator),
        ]);
    }

    public function walletHistory(Request $request): Response
    {
        abort_unless($request->user()->canAccessBusinessDashboard(), 403);

        $latestTransaction = $request->user()
            ->businessWalletTransactions()
            ->latest('id')
            ->first();

        $transactionsPaginator = $request->user()
            ->businessWalletTransactions()
            ->with('event:id,name')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $transactionsPaginator->setCollection(
            $transactionsPaginator->getCollection()->map(
                fn (BusinessWalletTransaction $transaction): array => $this->walletTransactionItem($transaction),
            ),
        );

        return Inertia::render('dashboard/BusinessWalletHistory', [
            'dashboardLinks' => [
                'overview' => route('dashboard.account'),
                'business' => route('dashboard.business'),
                'ownedEvents' => route('dashboard.account').'#events',
                'recentActivity' => route('dashboard.account').'#activity',
            ],
            'businessActionLinks' => [
                'createEvent' => route('dashboard.business.events.create'),
                'topUpWallet' => route('businesses'),
                'walletHistory' => route('dashboard.business.wallet.history'),
            ],
            'walletSummary' => [
                'currentBalance' => (int) ($request->user()->business_wallet_credits ?? 0),
                'currency' => (string) ($request->user()->business_wallet_currency ?? config('business.base_currency', 'EUR')),
                'totalTransactions' => (int) $request->user()->businessWalletTransactions()->count(),
                'creditsAdded' => (int) $request->user()->businessWalletTransactions()->where('credits', '>', 0)->sum('credits'),
                'creditsUsed' => abs((int) $request->user()->businessWalletTransactions()->where('credits', '<', 0)->sum('credits')),
                'latestActivityAt' => $latestTransaction?->created_at?->toIso8601String(),
            ],
            'walletTransactions' => $transactionsPaginator->items(),
            'walletTransactionsPagination' => $this->paginationMeta($transactionsPaginator),
            'sidebarLabel' => 'Business',
        ]);
    }

    public function startFilteredExports(BusinessDashboardRequest $request): RedirectResponse
    {
        abort_unless($request->user()->canAccessBusinessDashboard(), 403);

        $data = $this->accountData($request);
        $filters = $this->businessFilters($request);
        $filteredOwnedEvents = $this->filterBusinessOwnedEvents(
            collect($data['ownedEvents']),
            $filters,
        );
        $selectedOwnedEvents = $this->selectedBusinessOwnedEvents(
            $filteredOwnedEvents,
            $request,
        );

        if ($selectedOwnedEvents->isEmpty()) {
            return $this->businessRedirect($filters)
                ->with('info', 'No matching workspaces were selected for export.');
        }

        $ownedEventsById = $request->user()
            ->events()
            ->whereKey($selectedOwnedEvents->pluck('id')->all())
            ->get()
            ->keyBy('id');

        $started = 0;
        $alreadyRunning = 0;
        $withoutApprovedMedia = 0;

        foreach ($selectedOwnedEvents as $eventCard) {
            $event = $ownedEventsById->get($eventCard['id'] ?? null);
            if (! $event instanceof Event) {
                continue;
            }

            if ((int) ($eventCard['approvedCount'] ?? 0) === 0) {
                $withoutApprovedMedia++;

                continue;
            }

            $event->refresh();
            if (in_array($event->media_export_status, ['pending', 'processing'], true)) {
                $alreadyRunning++;

                continue;
            }

            $this->invalidateEventMediaExport($event, deleteStoredFile: true);
            $token = Str::uuid()->toString();

            $event->forceFill([
                'media_export_status' => 'pending',
                'media_export_token' => $token,
                'media_export_requested_at' => now(),
                'media_export_started_at' => null,
                'media_export_completed_at' => null,
                'media_export_failed_at' => null,
                'media_export_error' => null,
            ])->save();

            GenerateEventMediaExport::dispatch($event->id, $token);
            $started++;
        }

        $message = collect([
            $started > 0 ? "{$started} export".($started === 1 ? '' : 's').' started.' : null,
            $alreadyRunning > 0 ? "{$alreadyRunning} already running." : null,
            $withoutApprovedMedia > 0 ? "{$withoutApprovedMedia} skipped with no approved media." : null,
        ])->filter()->implode(' ');

        return $this->businessRedirect($filters)
            ->with($started > 0 ? 'success' : 'info', $message !== '' ? $message : 'No exports were started.');
    }

    public function downloadBillingQueue(BusinessDashboardRequest $request): StreamedResponse
    {
        abort_unless($request->user()->canAccessBusinessDashboard(), 403);

        $data = $this->accountData($request);
        $filters = $this->businessFilters($request);
        $filteredOwnedEvents = $this->filterBusinessOwnedEvents(
            collect($data['ownedEvents']),
            $filters,
        );
        $selectedOwnedEvents = $this->selectedBusinessOwnedEvents(
            $filteredOwnedEvents,
            $request,
        );

        $filename = 'business-billing-queue-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($selectedOwnedEvents): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'Event',
                'Plan',
                'Status',
                'Billing',
                'Payment Due',
                'Approved Uploads',
                'Pending Review',
                'Export Status',
                'Billing Link',
                'Workspace Link',
            ]);

            foreach ($selectedOwnedEvents as $event) {
                fputcsv($handle, [
                    $event['name'] ?? '',
                    $event['plan'] ?? '',
                    $event['statusLabel'] ?? '',
                    $event['billingLabel'] ?? '',
                    $event['paymentDueAt'] ?? '',
                    $event['approvedCount'] ?? 0,
                    $event['processingCount'] ?? 0,
                    $event['mediaExportLabel'] ?? '',
                    $event['links']['billing'] ?? '',
                    $event['links']['dashboard'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function ownedEvents(Request $request): RedirectResponse
    {
        return redirect()->to(route('dashboard').'#events');
    }

    public function recentActivity(Request $request): RedirectResponse
    {
        return redirect()->to(route('dashboard').'#activity');
    }

    /**
     * @return array<string, mixed>
     */
    private function accountData(Request $request): array
    {
        $isSuperAdmin = $request->user()->canAccessAdmin();
        $canAccessBusinessDashboard = $request->user()->canAccessBusinessDashboard();
        $accountOverviewUrl = $isSuperAdmin || $canAccessBusinessDashboard
            ? route('dashboard.account')
            : route('dashboard');

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
            'businessOverview' => $this->businessOverview($ownedEvents, $ownedEventCards->all()),
            'walletActivity' => $this->walletActivityItems($request),
            'businessAttentionEvents' => $this->businessAttentionEvents($ownedEvents, $assetStats, $defaultStats),
            'quickActions' => array_values(array_filter([
                $continueSetupEvent !== null ? [
                    'label' => 'Continue setup',
                    'description' => 'Finish onboarding so guests can start uploading.',
                    'url' => $this->onboardingStepUrl($continueSetupEvent),
                    'tone' => 'amber',
                ] : null,
                $isSuperAdmin ? [
                    'label' => 'Open admin',
                    'description' => 'Review accounts, events, and billing across the platform.',
                    'url' => route('admin.overview'),
                    'tone' => 'violet',
                ] : null,
            ])),
            'continueSetupEvent' => $continueSetupEvent !== null
                ? $this->dashboardEventCard(
                    $continueSetupEvent,
                    'owner',
                    null,
                    $assetStats[$continueSetupEvent->id] ?? $defaultStats,
                )
                : null,
            'accountNavigation' => array_values(array_filter([
                $canAccessBusinessDashboard ? [
                    'title' => 'Business',
                    'href' => route('dashboard.business'),
                ] : [
                    'title' => 'Events',
                    'href' => $accountOverviewUrl,
                ],
                $canAccessBusinessDashboard ? [
                    'title' => 'Create',
                    'href' => route('dashboard.business.events.create'),
                ] : null,
                $canAccessBusinessDashboard ? [
                    'title' => 'Wallet',
                    'href' => route('dashboard.business.wallet.history'),
                ] : null,
                $canAccessBusinessDashboard ? [
                    'title' => 'Portfolio',
                    'href' => route('dashboard.account'),
                ] : null,
                $isSuperAdmin ? [
                    'title' => 'Admin',
                    'href' => route('admin.overview'),
                ] : null,
            ])),
            'dashboardLinks' => [
                'overview' => $accountOverviewUrl,
                'business' => $canAccessBusinessDashboard ? route('dashboard.business') : null,
                'ownedEvents' => $accountOverviewUrl.'#events',
                'recentActivity' => $accountOverviewUrl.'#activity',
            ],
            'ownedEvents' => $ownedEventCards->all(),
            'collaboratorEvents' => $collaboratorEventCards->all(),
            'recentActivity' => $recentActivity,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function walletActivityItems(Request $request): array
    {
        if (! $request->user()->canAccessBusinessDashboard()) {
            return [];
        }

        return $request->user()
            ->businessWalletTransactions()
            ->with('event:id,name')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (BusinessWalletTransaction $transaction): array => $this->walletTransactionItem($transaction))
            ->all();
    }

    /**
     * @return array<string, int|string|null>
     */
    private function walletTransactionItem(BusinessWalletTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'kind' => $transaction->kind,
            'credits' => (int) $transaction->credits,
            'description' => $transaction->description,
            'createdAt' => $transaction->created_at?->toIso8601String(),
            'eventName' => $transaction->event?->name,
            'eventUrl' => $transaction->event !== null ? route('events.show', $transaction->event) : null,
        ];
    }

    /**
     * @param  Collection<int, Event>  $ownedEvents
     * @param  array<int, array<string, mixed>>  $ownedEventCards
     * @return array<string, int|bool>
     */
    private function businessOverview(Collection $ownedEvents, array $ownedEventCards): array
    {
        $totalAllocatedStorageBytes = (int) $ownedEvents->sum('storage_limit_bytes');
        $totalUsedStorageBytes = (int) $ownedEvents->sum('storage_used_bytes');
        $totalFreeStorageBytes = max(0, $totalAllocatedStorageBytes - $totalUsedStorageBytes);

        return [
            'hasOwnedEvents' => $ownedEvents->isNotEmpty(),
            'activeEventCount' => $ownedEvents
                ->filter(fn (Event $event): bool => in_array($event->status, [
                    Event::STATUS_DRAFT,
                    Event::STATUS_SCHEDULED,
                    Event::STATUS_LIVE,
                    Event::STATUS_GRACE,
                ], true))
                ->count(),
            'liveEventCount' => $ownedEvents
                ->filter(fn (Event $event): bool => $event->status === Event::STATUS_LIVE)
                ->count(),
            'unpaidEventCount' => $ownedEvents
                ->filter(fn (Event $event): bool => ! $event->is_paid)
                ->count(),
            'overdueEventCount' => $ownedEvents
                ->filter(function (Event $event): bool {
                    if ($event->is_paid) {
                        return false;
                    }

                    $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;

                    return $paymentDueAt?->isPast() ?? false;
                })
                ->count(),
            'readyExportCount' => collect($ownedEventCards)
                ->filter(fn (array $card): bool => ($card['mediaExportStatus'] ?? null) === 'ready')
                ->count(),
            'walletCredits' => (int) (auth()->user()?->business_wallet_credits ?? 0),
            'walletCurrency' => (string) (auth()->user()?->business_wallet_currency ?? config('business.base_currency', 'EUR')),
            'totalAllocatedStorageBytes' => $totalAllocatedStorageBytes,
            'totalUsedStorageBytes' => $totalUsedStorageBytes,
            'totalFreeStorageBytes' => $totalFreeStorageBytes,
            'storageUsagePercent' => $totalAllocatedStorageBytes > 0
                ? (int) min(100, round(($totalUsedStorageBytes / $totalAllocatedStorageBytes) * 100))
                : 0,
        ];
    }

    /**
     * @param  Collection<int, Event>  $ownedEvents
     * @return list<array<string, mixed>>
     */
    private function businessAttentionEvents(Collection $ownedEvents, array $assetStats, array $defaultStats): array
    {
        return $ownedEvents
            ->filter(fn (Event $event): bool => $this->eventNeedsBusinessAttention($event))
            ->sortBy(fn (Event $event): int => $this->businessAttentionPriority($event))
            ->take(4)
            ->map(function (Event $event) use ($assetStats, $defaultStats): array {
                [$statusLabel, $statusTone] = $this->eventStatusMeta($event);
                [$billingLabel, $billingTone] = $this->billingMeta($event);

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'plan' => $event->plan?->name ?? 'Custom plan',
                    'statusLabel' => $statusLabel,
                    'statusTone' => $statusTone,
                    'billingLabel' => $billingLabel,
                    'billingTone' => $billingTone,
                    'attentionLabel' => $this->businessAttentionLabel($event),
                    'attentionDetail' => $this->businessAttentionDetail($event),
                    'paymentDueAt' => ($event->payment_due_at ?? $event->grace_ends_at)?->toIso8601String(),
                    'storageUsedBytes' => (int) $event->storage_used_bytes,
                    'storageLimitBytes' => (int) $event->storage_limit_bytes,
                    'assetCount' => (int) (($assetStats[$event->id] ?? $defaultStats)['asset_count'] ?? 0),
                    'links' => [
                        'dashboard' => route('events.show', $event),
                        'media' => route('events.media', $event),
                        'settings' => route('events.settings', $event),
                        'billing' => route('events.settings', ['event' => $event, 'tab' => 'billing']),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function eventNeedsBusinessAttention(Event $event): bool
    {
        if ($event->onboarding_completed_at === null) {
            return true;
        }

        if (! $event->is_paid) {
            return true;
        }

        if (in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true)) {
            return true;
        }

        return $event->media_export_status === 'failed';
    }

    private function businessAttentionPriority(Event $event): int
    {
        if ($event->onboarding_completed_at === null) {
            return 0;
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;

        if (! $event->is_paid && ($paymentDueAt?->isPast() ?? false)) {
            return 1;
        }

        if (in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true)) {
            return 2;
        }

        if (! $event->is_paid) {
            return 3;
        }

        if ($event->media_export_status === 'failed') {
            return 4;
        }

        return 5;
    }

    private function businessAttentionLabel(Event $event): string
    {
        if ($event->onboarding_completed_at === null) {
            return 'Finish setup';
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;

        if (! $event->is_paid && ($paymentDueAt?->isPast() ?? false)) {
            return 'Resolve billing';
        }

        if (in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true)) {
            return 'Review access';
        }

        if (! $event->is_paid) {
            return 'Review invoice';
        }

        if ($event->media_export_status === 'failed') {
            return 'Retry export';
        }

        return 'Open workspace';
    }

    private function businessAttentionDetail(Event $event): string
    {
        if ($event->onboarding_completed_at === null) {
            return 'Complete onboarding so the album, upload flow, and workspace can go live.';
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;

        if (! $event->is_paid && ($paymentDueAt?->isPast() ?? false)) {
            return 'Payment is past due for this event and needs follow-up before access tightens further.';
        }

        if (in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true)) {
            return 'This event is no longer fully accessible and may need billing or cleanup follow-up.';
        }

        if (! $event->is_paid) {
            return 'This event still has an open balance. Double-check the due date and billing note.';
        }

        if ($event->media_export_status === 'failed') {
            return 'The latest export attempt failed. Open the workspace to retry or review the media set.';
        }

        return 'Open the workspace to keep this event moving.';
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
        [$roleLabel, $roleTone] = $this->eventRoleMeta($context, $collaboration);
        [$statusLabel, $statusTone] = $this->eventStatusMeta($event);
        [$billingLabel, $billingTone] = $this->billingMeta($event);
        [$mediaExportLabel, $mediaExportTone] = $this->mediaExportMeta($event);

        return [
            'id' => $event->id,
            'name' => $event->name,
            'plan' => $event->plan?->name ?? 'Custom plan',
            'eventDate' => $event->event_date?->toDateString(),
            'timezone' => $event->timezone,
            'roleLabel' => $roleLabel,
            'roleTone' => $roleTone,
            'statusLabel' => $statusLabel,
            'statusTone' => $statusTone,
            'billingLabel' => $billingLabel,
            'billingTone' => $billingTone,
            'mediaExportStatus' => is_string($event->media_export_status) && $event->media_export_status !== ''
                ? $event->media_export_status
                : 'idle',
            'mediaExportLabel' => $mediaExportLabel,
            'mediaExportTone' => $mediaExportTone,
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
                'label' => $event->onboarding_completed_at === null ? 'Continue setup' : 'Open workspace',
                'url' => $event->onboarding_completed_at === null
                    ? $this->onboardingStepUrl($event)
                    : route('events.show', $event),
            ],
            'links' => [
                'dashboard' => route('events.show', $event),
                'media' => route('events.media', $event),
                'settings' => route('events.settings', $event),
                'billing' => route('events.settings', ['event' => $event, 'tab' => 'billing']),
                'album' => route('events.album', $event->share_token),
                'wall' => route('events.wall', $event->share_token),
                'mediaExportDownload' => route('events.exports.media.download', $event),
            ],
            'canManage' => $context === 'owner' || $collaboration?->role === 'manager',
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function eventRoleMeta(string $context, ?EventCollaborator $collaboration): array
    {
        if ($context === 'owner') {
            return ['Owner', 'dark'];
        }

        return match ($collaboration?->role) {
            'manager' => ['Manager', 'sky'],
            default => ['Viewer', 'zinc'],
        };
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
    private function mediaExportMeta(Event $event): array
    {
        return match ($event->media_export_status) {
            'ready' => ['Export ready', 'emerald'],
            'pending', 'processing' => ['Export running', 'sky'],
            'failed' => ['Export failed', 'rose'],
            default => ['No export yet', 'zinc'],
        };
    }

    private function onboardingStepUrl(Event $event): string
    {
        return match ($event->onboarding_step) {
            'creating' => route('onboarding.creating', $event),
            'photos' => route('onboarding.photos', $event),
            default => route('onboarding.create'),
        };
    }

    private function singleAccessibleEvent(Request $request): ?Event
    {
        if ($request->user()->canAccessBusinessDashboard()) {
            return null;
        }

        $ownedEvents = $request->user()
            ->events()
            ->latest('id')
            ->limit(2)
            ->get();

        if ($ownedEvents->count() > 1) {
            return null;
        }

        $collaboratorEventIds = EventCollaborator::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'accepted'])
            ->whereNotIn('event_id', $ownedEvents->pluck('id')->all())
            ->latest('id')
            ->limit(2)
            ->pluck('event_id');

        if ($ownedEvents->count() + $collaboratorEventIds->count() !== 1) {
            return null;
        }

        if ($ownedEvents->count() === 1) {
            return $ownedEvents->first();
        }

        $collaboratorEventId = $collaboratorEventIds->first();

        return $collaboratorEventId !== null
            ? Event::query()->find($collaboratorEventId)
            : null;
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

    /**
     * @return array{search: string, status: string, selectionScope: string}
     */
    private function businessFilters(BusinessDashboardRequest $request): array
    {
        $validated = $request->validated();
        $status = is_string($validated['status'] ?? null)
            ? $validated['status']
            : self::BUSINESS_STATUS_ALL;
        $search = is_string($validated['search'] ?? null)
            ? trim($validated['search'])
            : '';
        $selectionScope = ($validated['selection_scope'] ?? null) === self::BUSINESS_SELECTION_SCOPE_ALL_FILTERED
            ? self::BUSINESS_SELECTION_SCOPE_ALL_FILTERED
            : self::BUSINESS_SELECTION_SCOPE_NONE;

        return [
            'search' => $search,
            'status' => in_array($status, $this->businessStatusValues(), true)
                ? $status
                : self::BUSINESS_STATUS_ALL,
            'selectionScope' => $selectionScope,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $ownedEvents
     * @param  Collection<int, array<string, mixed>>  $attentionEvents
     * @param  array{search: string, status: string, selectionScope: string}  $filters
     * @return array<string, mixed>
     */
    private function businessFilterMeta(
        Collection $ownedEvents,
        Collection $attentionEvents,
        array $filters,
    ): array {
        $filteredAttentionEvents = $this->filterBusinessAttentionEvents(
            $attentionEvents,
            $filters,
        );
        $statusCounts = $this->businessStatusCounts($ownedEvents);

        return [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'selectionScope' => $filters['selectionScope'],
            'hasActiveFilters' => $filters['search'] !== ''
                || $filters['status'] !== self::BUSINESS_STATUS_ALL,
            'ownedEventCount' => $this->filterBusinessOwnedEvents(
                $ownedEvents,
                $filters,
            )->count(),
            'ownedEventTotalCount' => $ownedEvents->count(),
            'attentionCount' => $filteredAttentionEvents->count(),
            'attentionTotalCount' => $attentionEvents->count(),
            'statusOptions' => [
                [
                    'value' => self::BUSINESS_STATUS_ALL,
                    'label' => 'All workspaces',
                    'count' => $statusCounts[self::BUSINESS_STATUS_ALL],
                ],
                [
                    'value' => self::BUSINESS_STATUS_ATTENTION,
                    'label' => 'Needs attention',
                    'count' => $statusCounts[self::BUSINESS_STATUS_ATTENTION],
                ],
                [
                    'value' => self::BUSINESS_STATUS_UNPAID,
                    'label' => 'Unpaid',
                    'count' => $statusCounts[self::BUSINESS_STATUS_UNPAID],
                ],
                [
                    'value' => self::BUSINESS_STATUS_OVERDUE,
                    'label' => 'Overdue',
                    'count' => $statusCounts[self::BUSINESS_STATUS_OVERDUE],
                ],
                [
                    'value' => self::BUSINESS_STATUS_LIVE,
                    'label' => 'Live now',
                    'count' => $statusCounts[self::BUSINESS_STATUS_LIVE],
                ],
                [
                    'value' => self::BUSINESS_STATUS_EXPORT_READY,
                    'label' => 'Exports ready',
                    'count' => $statusCounts[self::BUSINESS_STATUS_EXPORT_READY],
                ],
            ],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $ownedEvents
     * @return array<string, int>
     */
    private function businessStatusCounts(Collection $ownedEvents): array
    {
        return [
            self::BUSINESS_STATUS_ALL => $ownedEvents->count(),
            self::BUSINESS_STATUS_ATTENTION => $ownedEvents
                ->filter(fn (array $event): bool => $this->dashboardEventNeedsBusinessAttention($event))
                ->count(),
            self::BUSINESS_STATUS_UNPAID => $ownedEvents
                ->filter(fn (array $event): bool => ! (bool) ($event['isPaid'] ?? false))
                ->count(),
            self::BUSINESS_STATUS_OVERDUE => $ownedEvents
                ->filter(fn (array $event): bool => ($event['billingLabel'] ?? null) === 'Payment overdue')
                ->count(),
            self::BUSINESS_STATUS_LIVE => $ownedEvents
                ->filter(fn (array $event): bool => ($event['statusLabel'] ?? null) === 'Live now')
                ->count(),
            self::BUSINESS_STATUS_EXPORT_READY => $ownedEvents
                ->filter(fn (array $event): bool => ($event['mediaExportStatus'] ?? null) === 'ready')
                ->count(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $ownedEvents
     * @param  array{search: string, status: string, selectionScope: string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function filterBusinessOwnedEvents(Collection $ownedEvents, array $filters): Collection
    {
        return $ownedEvents
            ->filter(fn (array $event): bool => $this->businessEventMatchesSearch($event, $filters['search']))
            ->filter(fn (array $event): bool => $this->businessEventMatchesStatus($event, $filters['status']))
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $attentionEvents
     * @param  array{search: string, status: string, selectionScope: string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function filterBusinessAttentionEvents(Collection $attentionEvents, array $filters): Collection
    {
        return $attentionEvents
            ->filter(fn (array $event): bool => $this->businessAttentionMatchesSearch($event, $filters['search']))
            ->filter(function (array $event) use ($filters): bool {
                return match ($filters['status']) {
                    self::BUSINESS_STATUS_UNPAID => ($event['billingLabel'] ?? null) !== 'Paid',
                    self::BUSINESS_STATUS_OVERDUE => ($event['billingLabel'] ?? null) === 'Payment overdue',
                    default => true,
                };
            })
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $ownedEvents
     */
    private function paginateBusinessOwnedEvents(
        Collection $ownedEvents,
        BusinessDashboardRequest $request,
        int $perPage = 6,
    ): LengthAwarePaginator {
        $page = max(1, (int) $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            $ownedEvents->forPage($page, $perPage)->values()->all(),
            $ownedEvents->count(),
            $perPage,
            $page,
            [
                'path' => route('dashboard.business'),
                'pageName' => 'page',
            ],
        );

        return $paginator->appends($request->except('page'));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $ownedEvents
     * @return Collection<int, array<string, mixed>>
     */
    private function selectedBusinessOwnedEvents(
        Collection $ownedEvents,
        BusinessDashboardRequest $request,
    ): Collection {
        $validated = $request->validated();

        if (($validated['selection_scope'] ?? null) === self::BUSINESS_SELECTION_SCOPE_ALL_FILTERED) {
            return $ownedEvents->values();
        }

        $selectedIds = collect($validated['event_ids'] ?? [])
            ->filter(fn (mixed $id): bool => is_int($id) || (is_string($id) && is_numeric($id)))
            ->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return $ownedEvents->values();
        }

        return $ownedEvents
            ->filter(fn (array $event): bool => $selectedIds->contains((int) ($event['id'] ?? 0)))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'prevPageUrl' => $paginator->previousPageUrl(),
            'nextPageUrl' => $paginator->nextPageUrl(),
            'links' => $paginator->linkCollection()
                ->map(fn (array $link): array => [
                    'url' => $link['url'],
                    'label' => strip_tags((string) $link['label']),
                    'active' => (bool) $link['active'],
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function businessEventMatchesSearch(array $event, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $needle = mb_strtolower($search);

        return collect([
            $event['name'] ?? '',
            $event['plan'] ?? '',
            $event['statusLabel'] ?? '',
            $event['billingLabel'] ?? '',
            $event['mediaExportLabel'] ?? '',
            $event['timezone'] ?? '',
        ])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->contains(fn (string $value): bool => str_contains(mb_strtolower($value), $needle));
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function businessAttentionMatchesSearch(array $event, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $needle = mb_strtolower($search);

        return collect([
            $event['name'] ?? '',
            $event['plan'] ?? '',
            $event['statusLabel'] ?? '',
            $event['billingLabel'] ?? '',
            $event['attentionLabel'] ?? '',
            $event['attentionDetail'] ?? '',
        ])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->contains(fn (string $value): bool => str_contains(mb_strtolower($value), $needle));
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function businessEventMatchesStatus(array $event, string $status): bool
    {
        return match ($status) {
            self::BUSINESS_STATUS_ATTENTION => $this->dashboardEventNeedsBusinessAttention($event),
            self::BUSINESS_STATUS_UNPAID => ! (bool) ($event['isPaid'] ?? false),
            self::BUSINESS_STATUS_OVERDUE => ($event['billingLabel'] ?? null) === 'Payment overdue',
            self::BUSINESS_STATUS_LIVE => ($event['statusLabel'] ?? null) === 'Live now',
            self::BUSINESS_STATUS_EXPORT_READY => ($event['mediaExportStatus'] ?? null) === 'ready',
            default => true,
        };
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function dashboardEventNeedsBusinessAttention(array $event): bool
    {
        if (! (bool) ($event['onboardingComplete'] ?? false)) {
            return true;
        }

        if (! (bool) ($event['isPaid'] ?? false)) {
            return true;
        }

        if (in_array($event['statusLabel'] ?? null, ['Locked', 'Expired'], true)) {
            return true;
        }

        return ($event['mediaExportStatus'] ?? null) === 'failed';
    }

    /**
     * @return list<string>
     */
    private function businessStatusValues(): array
    {
        return [
            self::BUSINESS_STATUS_ALL,
            self::BUSINESS_STATUS_ATTENTION,
            self::BUSINESS_STATUS_UNPAID,
            self::BUSINESS_STATUS_OVERDUE,
            self::BUSINESS_STATUS_LIVE,
            self::BUSINESS_STATUS_EXPORT_READY,
        ];
    }

    /**
     * @param  array{search: string, status: string, selectionScope: string}  $filters
     */
    private function businessRedirect(array $filters): RedirectResponse
    {
        return to_route('dashboard.business', array_filter([
            'search' => $filters['search'] !== '' ? $filters['search'] : null,
            'status' => $filters['status'] !== self::BUSINESS_STATUS_ALL ? $filters['status'] : null,
            'selection_scope' => $filters['selectionScope'] === self::BUSINESS_SELECTION_SCOPE_ALL_FILTERED
                ? self::BUSINESS_SELECTION_SCOPE_ALL_FILTERED
                : null,
        ]));
    }

    private function invalidateEventMediaExport(Event $event, bool $deleteStoredFile = true): void
    {
        if (
            $deleteStoredFile
            && is_string($event->media_export_disk)
            && $event->media_export_disk !== ''
            && is_string($event->media_export_path)
            && $event->media_export_path !== ''
        ) {
            Storage::disk($event->media_export_disk)->delete($event->media_export_path);
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
}
