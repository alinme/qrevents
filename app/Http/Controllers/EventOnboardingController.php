<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventOnboardingRequest;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use App\Support\BusinessPlanCatalog;
use App\Support\BusinessWalletManager;
use App\Support\EventLifecycleWindows;
use App\Support\IsgdShortUrlManager;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EventOnboardingController extends Controller
{
    public function __construct(
        private BusinessPlanCatalog $businessPlanCatalog,
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user() === null) {
            return redirect()->guest(route('register', absolute: false));
        }

        if ($request->user()->isBusinessAccount()) {
            return $request->user()->hasCompletedBusinessOnboarding()
                ? to_route('dashboard.business')
                : to_route('dashboard.business.onboarding');
        }

        if ($request->boolean('restart')) {
            return Inertia::render('onboarding/Create', $this->onboardingCreateProps($request));
        }

        $latestEvent = $request->user()->events()->latest('id')->first();

        if ($latestEvent !== null) {
            return $latestEvent->onboarding_completed_at === null
                ? $this->redirectToStep($latestEvent)
                : to_route('dashboard.account');
        }

        return Inertia::render('onboarding/Create', $this->onboardingCreateProps($request));
    }

    public function createBusiness(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null && $user->isBusinessAccount(), 403);

        if (! $user->hasCompletedBusinessOnboarding()) {
            return to_route('dashboard.business.onboarding');
        }

        return Inertia::render('onboarding/Create', $this->onboardingCreateProps($request, true));
    }

    public function store(StoreEventOnboardingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        abort_unless($user !== null, 403);
        abort_if($user->isBusinessAccount(), 403);

        $event = $this->createEventFromValidatedData($user, $validated);
        $event->update([
            'onboarding_step' => 'photos',
        ]);

        return to_route('onboarding.photos', $event)->with('success', 'Event created.');
    }

    public function storeBusiness(
        StoreEventOnboardingRequest $request,
        BusinessWalletManager $businessWalletManager,
    ): RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();
        abort_unless($user !== null && $user->isBusinessAccount(), 403);
        abort_unless($user->hasCompletedBusinessOnboarding(), 403);

        $plan = $this->resolveSelectedPlan($validated['plan_slug'] ?? null, true);
        if (! $businessWalletManager->canAffordPlan($user, $plan)) {
            $availableCredits = (int) $user->business_wallet_credits;
            $requiredCredits = $businessWalletManager->planCreditCost($plan);
            $missingCredits = max($requiredCredits - $availableCredits, 0);

            throw ValidationException::withMessages([
                'plan_slug' => "This {$plan->name} event needs {$requiredCredits} credits. Your wallet has {$availableCredits}, so top up {$missingCredits} more first.",
            ]);
        }

        $event = DB::transaction(function () use ($user, $validated, $plan, $businessWalletManager): Event {
            $event = $this->createEventFromValidatedData($user, $validated, $plan, true);
            $businessWalletManager->debitForEvent($user, $event, $plan);
            $event->update([
                'onboarding_step' => 'photos',
            ]);

            return $event;
        });

        return to_route('onboarding.photos', $event)->with('success', 'Event created.');
    }

    public function creating(Request $request, Event $event): RedirectResponse
    {
        $this->assertOwnership($request, $event);

        if (in_array($event->onboarding_step, ['created', 'creating'], true)) {
            $event->update([
                'onboarding_step' => 'photos',
            ]);
        }

        return to_route('onboarding.photos', $event);
    }

    public function photos(Request $request, Event $event): Response
    {
        $this->assertOwnership($request, $event);

        $businessMode = $request->user()?->isBusinessAccount() === true;

        $event->update([
            'onboarding_step' => 'completed',
            'onboarding_completed_at' => $event->onboarding_completed_at ?? now(),
        ]);

        $request->session()->flash('show_dashboard_modal', true);

        $albumUrl = route('events.album', $event->publicAlbumCode());
        $wallUrl = route('events.wall', $event->publicAlbumCode());
        $publicShortLinks = app(IsgdShortUrlManager::class)->forEvent($event);

        return Inertia::render('onboarding/FirstPhotos', [
            'eventName' => $event->name,
            'albumUrl' => $albumUrl,
            'albumShortUrl' => $publicShortLinks['albumShortUrl'],
            'albumAccessCode' => $event->publicAlbumCode(),
            'albumAccessEntryUrl' => route('events.album.access.show'),
            'albumAccessShortcutUrl' => 'https://is.gd/evsmrt',
            'wallUrl' => $wallUrl,
            'wallShortUrl' => $publicShortLinks['wallShortUrl'],
            'qrCodeDataUrl' => $this->createQrCodeDataUrl($albumUrl),
            'businessMode' => $businessMode,
            'dashboardUrl' => $businessMode ? route('dashboard.business') : route('dashboard.account'),
        ]);
    }

    public function ready(Request $request, Event $event): RedirectResponse
    {
        $this->assertOwnership($request, $event);

        if ($event->onboarding_completed_at === null) {
            $event->update([
                'onboarding_step' => 'completed',
                'onboarding_completed_at' => now(),
            ]);
        }

        $request->session()->flash('show_dashboard_modal', true);

        return to_route('dashboard.account');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function initialBranding(array $validated): array
    {
        if (($validated['type'] ?? null) !== 'wedding') {
            return [];
        }

        $partnerOneFirstName = trim((string) ($validated['wedding_partner_one_first_name'] ?? ''));
        $partnerTwoFirstName = trim((string) ($validated['wedding_partner_two_first_name'] ?? ''));
        $familyName = trim((string) ($validated['wedding_family_name'] ?? ''));

        if ($partnerOneFirstName === '' || $partnerTwoFirstName === '' || $familyName === '') {
            return [];
        }

        return [
            'event_naming' => [
                'partner_one_first_name' => $partnerOneFirstName,
                'partner_two_first_name' => $partnerTwoFirstName,
                'family_name' => $familyName,
            ],
        ];
    }

    private function assertOwnership(Request $request, Event $event): void
    {
        abort_unless($event->user_id === $request->user()->id, 403);
    }

    private function redirectToStep(Event $event): RedirectResponse
    {
        return match ($event->onboarding_step) {
            'creating' => to_route('onboarding.photos', $event),
            'photos' => to_route('onboarding.photos', $event),
            'completed' => to_route('dashboard.account'),
            default => to_route('onboarding.photos', $event),
        };
    }

    private function resolveSelectedPlan(?string $slug, bool $businessMode = false): Plan
    {
        $normalizedSlug = is_string($slug) ? Str::lower(trim($slug)) : '';

        if ($normalizedSlug !== '') {
            $selectedPlan = $businessMode
                ? $this->businessPlanCatalog->query()->where('slug', $normalizedSlug)->first()
                : Plan::query()
                    ->where('slug', $normalizedSlug)
                    ->where('is_active', true)
                    ->first();

            if ($selectedPlan !== null) {
                return $selectedPlan;
            }

            abort_if($businessMode, 422, 'Choose an available business plan.');
        }

        $activeDefaultPlan = $businessMode
            ? $this->businessPlanCatalog->query()->where('is_default', true)->first()
            : Plan::query()
                ->where('is_active', true)
                ->where('is_default', true)
                ->orderBy('price_cents')
                ->first();

        if ($activeDefaultPlan !== null) {
            return $activeDefaultPlan;
        }

        $fallbackActivePlan = $businessMode
            ? $this->businessPlanCatalog->query()->first()
            : Plan::query()
                ->where('is_active', true)
                ->orderBy('price_cents')
                ->first();

        if ($fallbackActivePlan !== null) {
            return $fallbackActivePlan;
        }

        abort_if($businessMode, 500, 'Business plans are not configured.');

        return tap(
            Plan::query()->firstOrNew(['slug' => 'free']),
            function (Plan $plan): void {
                $plan->fill([
                    'name' => 'Free',
                    'description' => 'Small events with a lightweight album and simple setup.',
                    'currency' => 'EUR',
                    'price_cents' => 0,
                    'storage_limit_bytes' => 3221225472,
                    'upload_limit' => 100,
                    'retention_days' => 7,
                    'grace_days' => 0,
                    'upload_window_days' => 1,
                    'customization_tier' => 'basic',
                    'download_all_enabled' => false,
                    'moderation_tools_enabled' => false,
                    'remove_app_branding' => false,
                    'video_max_duration_seconds' => 30,
                    'photo_max_size_bytes' => 15728640,
                    'video_max_size_bytes' => 314572800,
                    'is_active' => true,
                    'is_default' => true,
                ]);
                $plan->save();
            },
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createEventFromValidatedData(
        User $user,
        array $validated,
        ?Plan $resolvedPlan = null,
        bool $businessMode = false,
    ): Event {
        $plan = $resolvedPlan ?? $this->resolveSelectedPlan($validated['plan_slug'] ?? null, $businessMode);
        $timezone = $validated['timezone'] ?? config('events.default_timezone', 'Europe/Bucharest');
        $eventDates = $this->normalizeEventDates($validated['event_dates'] ?? []);
        $subEvents = $this->normalizeSubEvents($validated['sub_events'] ?? []);
        $venueAddress = $this->resolveVenueAddress($subEvents);
        $eventDate = $this->resolvePrimaryEventDate($validated['event_date'] ?? null, $eventDates, $subEvents);
        $windows = EventLifecycleWindows::build(
            $eventDate,
            $timezone,
            (int) $plan->upload_window_days,
            (int) $plan->grace_days,
        );
        $branding = $this->initialBranding($validated);
        $isPaid = $businessMode || (int) $plan->price_cents === 0;
        $retentionEndsAt = $isPaid && $windows['upload_window_ends_at'] !== null
            ? $windows['upload_window_ends_at']->addDays((int) $plan->retention_days)->endOfDay()
            : null;

        /** @var Event $event */
        $event = $user->events()->create([
            'plan_id' => $plan->id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'venue_address' => $venueAddress,
            'event_date' => $eventDate,
            'event_dates' => $eventDates,
            'sub_events' => $subEvents,
            'timezone' => $timezone,
            'attendee_estimate' => (int) $validated['attendee_estimate'],
            'status' => $windows['status'],
            'onboarding_step' => 'creating',
            'currency' => $plan->currency,
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? now() : null,
            'branding' => $branding !== [] ? $branding : null,
            'payment_due_at' => $isPaid ? null : $windows['upload_window_starts_at'],
            'retention_ends_at' => $retentionEndsAt,
            'upload_window_starts_at' => $windows['upload_window_starts_at'],
            'upload_window_ends_at' => $windows['upload_window_ends_at'],
            'grace_ends_at' => $windows['grace_ends_at'],
            'hard_lock_at' => $windows['hard_lock_at'],
            'storage_limit_bytes' => $plan->storage_limit_bytes,
            'upload_limit' => $plan->upload_limit,
            'upload_window_days' => $plan->upload_window_days,
            'customization_tier' => $plan->customization_tier,
            'download_all_enabled' => $plan->download_all_enabled,
            'moderation_tools_enabled' => $plan->moderation_tools_enabled,
            'remove_app_branding' => $plan->remove_app_branding,
            'moderation_enabled' => $plan->moderation_tools_enabled,
            'auto_moderation_enabled' => $plan->moderation_tools_enabled,
            'video_max_duration_seconds' => $plan->video_max_duration_seconds,
            'photo_max_size_bytes' => $plan->photo_max_size_bytes,
            'video_max_size_bytes' => $plan->video_max_size_bytes,
            'share_token' => Str::random(32),
            'public_invitation_token' => Str::lower((string) Str::uuid()),
        ]);

        return $event;
    }

    private function createQrCodeDataUrl(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(320),
            new SvgImageBackEnd,
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($content);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * @param  array<int, array<string, mixed>>  $eventDates
     * @param  array<int, array<string, mixed>>  $subEvents
     */
    private function resolvePrimaryEventDate(?string $eventDate, array $eventDates, array $subEvents): ?string
    {
        if (is_string($eventDate) && trim($eventDate) !== '') {
            return $eventDate;
        }

        $firstEventDate = collect($eventDates)
            ->pluck('date')
            ->first(fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        if (is_string($firstEventDate) && trim($firstEventDate) !== '') {
            return $firstEventDate;
        }

        $firstSubEventDate = collect($subEvents)
            ->pluck('date')
            ->first(fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        return is_string($firstSubEventDate) && trim($firstSubEventDate) !== ''
            ? $firstSubEventDate
            : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $eventDates
     * @return array<int, array{label: string, date: string}>
     */
    private function normalizeEventDates(array $eventDates): array
    {
        return collect($eventDates)
            ->map(function (mixed $eventDate, int $index): ?array {
                if (! is_array($eventDate)) {
                    return null;
                }

                $date = is_string($eventDate['date'] ?? null) ? trim((string) $eventDate['date']) : '';
                if ($date === '') {
                    return null;
                }

                $label = is_string($eventDate['label'] ?? null) ? trim((string) $eventDate['label']) : '';

                return [
                    'label' => $label !== '' ? $label : 'Event day '.($index + 1),
                    'date' => $date,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $subEvents
     * @return array<int, array{key: string, label: string, date: string, start_time: string, address: string|null, no_address: bool}>
     */
    private function normalizeSubEvents(array $subEvents): array
    {
        return collect($subEvents)
            ->map(function (mixed $subEvent): ?array {
                if (! is_array($subEvent)) {
                    return null;
                }

                $key = is_string($subEvent['key'] ?? null) ? trim((string) $subEvent['key']) : '';
                $label = is_string($subEvent['label'] ?? null) ? trim((string) $subEvent['label']) : '';
                $date = is_string($subEvent['date'] ?? null) ? trim((string) $subEvent['date']) : '';
                $startTime = is_string($subEvent['start_time'] ?? null) ? trim((string) $subEvent['start_time']) : '';
                $address = is_string($subEvent['address'] ?? null) ? trim((string) $subEvent['address']) : '';
                $noAddress = filter_var($subEvent['no_address'] ?? false, FILTER_VALIDATE_BOOL);

                if ($key === '' || $label === '' || $date === '' || $startTime === '') {
                    return null;
                }

                return [
                    'key' => $key,
                    'label' => $label,
                    'date' => $date,
                    'start_time' => $startTime,
                    'address' => $noAddress || $address === '' ? null : $address,
                    'no_address' => $noAddress,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{key: string, label: string, date: string, start_time: string, address: string|null, no_address: bool}>  $subEvents
     */
    private function resolveVenueAddress(array $subEvents): ?string
    {
        $firstSubEventAddress = collect($subEvents)
            ->pluck('address')
            ->first(fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        return is_string($firstSubEventAddress) && trim($firstSubEventAddress) !== ''
            ? $firstSubEventAddress
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function onboardingCreateProps(Request $request, bool $businessMode = false): array
    {
        return [
            'defaultTimezone' => config('events.default_timezone'),
            'defaultPlanSlug' => $this->resolveSelectedPlan($request->query('plan'), $businessMode)->slug,
            'owner' => $request->user() === null
                ? null
                : [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            'businessMode' => $businessMode,
            'businessWalletCredits' => $businessMode && $request->user() !== null
                ? (int) $request->user()->business_wallet_credits
                : null,
            'businessTopUpUrl' => $businessMode
                ? route('dashboard.business.wallet.history')
                : null,
            'accountNavigation' => [],
            'businessNavigation' => $businessMode ? [
                [
                    'title' => 'Business',
                    'href' => route('dashboard.business'),
                ],
                [
                    'title' => 'Billing',
                    'href' => route('dashboard.business.wallet.history'),
                ],
                [
                    'title' => 'Events',
                    'href' => route('dashboard.business.events.index'),
                ],
            ] : [],
            'dashboardLinks' => $businessMode ? [
                'overview' => route('dashboard.account'),
                'business' => route('dashboard.business'),
                'createBusiness' => route('dashboard.business.events.create'),
            ] : null,
            'sidebarLabel' => $businessMode ? 'Business' : null,
            'submitUrl' => $businessMode
                ? route('dashboard.business.events.store')
                : route('onboarding.store'),
            'pricingPlans' => $this->pricingPlansPayload($businessMode),
            'eventTypes' => [
                [
                    'value' => 'wedding',
                    'label' => 'Wedding',
                    'description' => 'Capture every ceremony, dinner, and dance-floor memory in one polished flow.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'civil-ceremony',
                            'label' => 'Civil union',
                            'description' => 'The legal ceremony or city hall moment.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1520854221256-17451cc331bf?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => false,
                            'required' => false,
                        ],
                        [
                            'key' => 'church-ceremony',
                            'label' => 'Church ceremony',
                            'description' => 'The formal service before the celebration starts.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => false,
                            'required' => false,
                        ],
                        [
                            'key' => 'reception',
                            'label' => 'Reception',
                            'description' => 'Dinner, speeches, and the main celebration.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => false,
                            'required' => false,
                        ],
                        [
                            'key' => 'after-party',
                            'label' => 'After party',
                            'description' => 'The late-night continuation after the main reception.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => false,
                            'required' => false,
                        ],
                    ],
                ],
                [
                    'value' => 'party',
                    'label' => 'Party',
                    'description' => 'A single celebration flow with one main moment and one optional address.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'party',
                            'label' => 'Party',
                            'description' => 'The main party, launch, reunion, or celebration window.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => true,
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'value' => 'birthday',
                    'label' => 'Birthday',
                    'description' => 'One main birthday celebration with one address when needed.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'birthday',
                            'label' => 'Birthday celebration',
                            'description' => 'Cake, speeches, dinner, or the main birthday gathering.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => true,
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'value' => 'engagement',
                    'label' => 'Engagement',
                    'description' => 'One engagement celebration with one optional address when the event is online or private.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'engagement',
                            'label' => 'Engagement celebration',
                            'description' => 'Proposal party, dinner, toast, or the main engagement gathering.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => true,
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'value' => 'baptism',
                    'label' => 'Baptism',
                    'description' => 'Start with the church moment and the family celebration after it.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1516589091380-5d8e87df6999?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'church-ceremony',
                            'label' => 'Church ceremony',
                            'description' => 'The ceremony itself and the most meaningful moments.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1504198453319-5ce911bafcde?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => true,
                            'required' => true,
                        ],
                        [
                            'key' => 'family-party',
                            'label' => 'Family party',
                            'description' => 'The meal or reception after the ceremony.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1555244162-803834f70033?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => true,
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'value' => 'other',
                    'label' => 'Other',
                    'description' => 'A simple single-moment event for anything that does not fit the presets.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'main-event',
                            'label' => 'Main event',
                            'description' => 'The core session, dinner, launch, or featured moment.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80',
                            'defaultSelected' => true,
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pricingPlansPayload(bool $businessMode = false): array
    {
        $planQuery = $businessMode
            ? $this->businessPlanCatalog->query()
            : Plan::query()
                ->where('is_active', true)
                ->orderBy('price_cents');

        return $planQuery
            ->get()
            ->map(function (Plan $plan) use ($businessMode): array {
                return [
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'priceLabel' => (int) $plan->price_cents === 0
                        ? 'Free'
                        : sprintf('EUR %.2f', $plan->price_cents / 100),
                    'billingLabel' => (int) $plan->price_cents === 0 ? 'No CC required' : 'One-time fee',
                    'uploadLimitLabel' => (int) $plan->upload_limit >= 1000000
                        ? 'Unlimited uploads'
                        : number_format((int) $plan->upload_limit).' uploads',
                    'retentionLabel' => number_format((int) $plan->retention_days).' days saved',
                    'activeWindowLabel' => number_format((int) $plan->upload_window_days).' day access',
                    'customizationTier' => $plan->customization_tier,
                    'downloadAllEnabled' => (bool) $plan->download_all_enabled,
                    'moderationToolsEnabled' => (bool) $plan->moderation_tools_enabled,
                    'isDefault' => (bool) $plan->is_default,
                    'businessCreditCost' => $businessMode ? (int) ($plan->business_credit_cost ?? 0) : null,
                ];
            })
            ->values()
            ->all();
    }
}
