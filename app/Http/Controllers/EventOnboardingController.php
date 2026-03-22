<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventOnboardingRequest;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use App\Support\EventLifecycleWindows;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventOnboardingController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->boolean('restart')) {
            return Inertia::render('onboarding/Create', $this->onboardingCreateProps($request));
        }

        if ($request->user() === null) {
            return Inertia::render('onboarding/Create', $this->onboardingCreateProps($request));
        }

        $latestEvent = $request->user()->events()->latest('id')->first();

        if ($latestEvent !== null) {
            return $latestEvent->onboarding_completed_at === null
                ? $this->redirectToStep($latestEvent)
                : to_route('dashboard');
        }

        return Inertia::render('onboarding/Create', $this->onboardingCreateProps($request));
    }

    public function store(StoreEventOnboardingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user() ?? $this->createOwnerAccount($validated);
        $plan = $this->resolveSelectedPlan($validated['plan_slug'] ?? null);
        $timezone = $validated['timezone'] ?? config('events.default_timezone', 'Europe/Bucharest');
        $eventDates = $this->normalizeEventDates($validated['event_dates'] ?? []);
        $subEvents = $this->normalizeSubEvents($validated['sub_events'] ?? []);
        $eventDate = $this->resolvePrimaryEventDate($validated['event_date'] ?? null, $eventDates, $subEvents);
        $windows = EventLifecycleWindows::build(
            $eventDate,
            $timezone,
            (int) $plan->upload_window_days,
            (int) $plan->grace_days,
        );
        $branding = $this->initialBranding($validated);
        $isFreePlan = (int) $plan->price_cents === 0;

        $event = $user->events()->create([
            'plan_id' => $plan->id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'venue_address' => $validated['venue_address'],
            'event_date' => $eventDate,
            'event_dates' => $eventDates,
            'sub_events' => $subEvents,
            'timezone' => $timezone,
            'attendee_estimate' => (int) $validated['attendee_estimate'],
            'status' => $windows['status'],
            'onboarding_step' => 'creating',
            'currency' => $plan->currency,
            'is_paid' => $isFreePlan,
            'paid_at' => $isFreePlan ? now() : null,
            'branding' => $branding !== [] ? $branding : null,
            'payment_due_at' => $isFreePlan ? null : $windows['upload_window_starts_at'],
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
        ]);

        $user->syncConfiguredAccountType();

        return to_route('onboarding.creating', $event)->with('success', 'Event created.');
    }

    public function creating(Request $request, Event $event): Response
    {
        $this->assertOwnership($request, $event);

        if ($event->onboarding_step === 'created') {
            $event->update([
                'onboarding_step' => 'creating',
            ]);
        }

        return Inertia::render('onboarding/Creating', [
            'eventName' => $event->name,
            'nextUrl' => route('onboarding.photos', $event),
        ]);
    }

    public function photos(Request $request, Event $event): Response
    {
        $this->assertOwnership($request, $event);

        $event->update([
            'onboarding_step' => 'photos',
        ]);

        $albumUrl = route('events.album', $event->share_token);
        $wallUrl = route('events.wall', $event->share_token);

        return Inertia::render('onboarding/FirstPhotos', [
            'eventName' => $event->name,
            'albumUrl' => $albumUrl,
            'wallUrl' => $wallUrl,
            'qrCodeDataUrl' => $this->createQrCodeDataUrl($albumUrl),
            'readyUrl' => route('onboarding.ready', $event),
        ]);
    }

    public function ready(Request $request, Event $event): Response
    {
        $this->assertOwnership($request, $event);

        if ($event->onboarding_completed_at === null) {
            $event->update([
                'onboarding_step' => 'completed',
                'onboarding_completed_at' => now(),
            ]);
        }

        $request->session()->flash('show_dashboard_modal', true);

        return Inertia::render('onboarding/Ready', [
            'eventName' => $event->name,
            'dashboardUrl' => route('dashboard'),
        ]);
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
            'creating' => to_route('onboarding.creating', $event),
            'photos' => to_route('onboarding.photos', $event),
            'completed' => to_route('dashboard'),
            default => to_route('onboarding.creating', $event),
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createOwnerAccount(array $validated): User
    {
        $user = User::query()->create([
            'name' => trim((string) $validated['owner_name']),
            'email' => trim((string) $validated['owner_email']),
            'password' => (string) $validated['password'],
            'account_type' => User::ACCOUNT_TYPE_USER,
        ]);

        Auth::login($user);
        request()->session()->regenerate();

        return $user;
    }

    private function resolveSelectedPlan(?string $slug): Plan
    {
        $normalizedSlug = is_string($slug) ? Str::lower(trim($slug)) : '';

        if ($normalizedSlug !== '') {
            $selectedPlan = Plan::query()
                ->where('slug', $normalizedSlug)
                ->where('is_active', true)
                ->first();

            if ($selectedPlan !== null) {
                return $selectedPlan;
            }
        }

        $activeDefaultPlan = Plan::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('price_cents')
            ->first();

        if ($activeDefaultPlan !== null) {
            return $activeDefaultPlan;
        }

        $fallbackActivePlan = Plan::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->first();

        if ($fallbackActivePlan !== null) {
            return $fallbackActivePlan;
        }

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
     * @return array<int, array{key: string, label: string, date: string, start_time: string}>
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

                if ($key === '' || $label === '' || $date === '' || $startTime === '') {
                    return null;
                }

                return [
                    'key' => $key,
                    'label' => $label,
                    'date' => $date,
                    'start_time' => $startTime,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function onboardingCreateProps(Request $request): array
    {
        return [
            'defaultTimezone' => config('events.default_timezone'),
            'defaultPlanSlug' => $this->resolveSelectedPlan($request->query('plan'))->slug,
            'owner' => $request->user() === null
                ? null
                : [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            'pricingPlans' => $this->pricingPlansPayload(),
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
                        ],
                        [
                            'key' => 'church-ceremony',
                            'label' => 'Church ceremony',
                            'description' => 'The formal service before the celebration starts.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'reception',
                            'label' => 'Reception',
                            'description' => 'Dinner, speeches, and the main celebration.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'after-party',
                            'label' => 'After party',
                            'description' => 'The late-night continuation after the main reception.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
                        ],
                    ],
                ],
                [
                    'value' => 'party',
                    'label' => 'Party',
                    'description' => 'Great for private celebrations, launches, reunions, and one-night moments.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'guest-arrival',
                            'label' => 'Guest arrival',
                            'description' => 'Doors open, welcome drinks, and first photos.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'main-party',
                            'label' => 'Main party',
                            'description' => 'The peak dance-floor or social energy window.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'special-moment',
                            'label' => 'Special moment',
                            'description' => 'Toast, award, reveal, or featured highlight.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=80',
                        ],
                    ],
                ],
                [
                    'value' => 'birthday',
                    'label' => 'Birthday',
                    'description' => 'Perfect for birthdays with guests sharing candid photos all day or night.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'welcome-time',
                            'label' => 'Welcome time',
                            'description' => 'Meet-and-greet, food, and early arrivals.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'cake-cutting',
                            'label' => 'Cake cutting',
                            'description' => 'The signature birthday moment everyone wants to catch.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1464347744102-11db6282f854?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'birthday-party',
                            'label' => 'Main party',
                            'description' => 'Games, dancing, speeches, or the late celebration.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=900&q=80',
                        ],
                    ],
                ],
                [
                    'value' => 'engagement',
                    'label' => 'Engagement',
                    'description' => 'Turn your engagement celebration into a shared album guests can join instantly.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'proposal-moment',
                            'label' => 'Proposal moment',
                            'description' => 'The reveal, surprise, or headline photo moment.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'toast',
                            'label' => 'Toast',
                            'description' => 'Champagne, speeches, and first congratulations.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'engagement-party',
                            'label' => 'Engagement party',
                            'description' => 'Dinner or celebration after the announcement.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80',
                        ],
                    ],
                ],
                [
                    'value' => 'baptism',
                    'label' => 'Baptism',
                    'description' => 'Collect memories from the service and family gathering without extra friction.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1516589091380-5d8e87df6999?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'service',
                            'label' => 'Service',
                            'description' => 'The ceremony itself and the most meaningful moments.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1504198453319-5ce911bafcde?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'family-lunch',
                            'label' => 'Family lunch',
                            'description' => 'The meal or reception after the ceremony.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1555244162-803834f70033?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'celebration',
                            'label' => 'Celebration',
                            'description' => 'A more relaxed gathering with family and friends.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=900&q=80',
                        ],
                    ],
                ],
                [
                    'value' => 'other',
                    'label' => 'Other',
                    'description' => 'For conferences, launches, dinners, reunions, and any event that needs shared media.',
                    'imageUrl' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                    'subEvents' => [
                        [
                            'key' => 'opening',
                            'label' => 'Opening',
                            'description' => 'Doors open, welcome, or registration start.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'main-session',
                            'label' => 'Main session',
                            'description' => 'The core presentation, dinner, or featured moment.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80',
                        ],
                        [
                            'key' => 'closing',
                            'label' => 'Closing',
                            'description' => 'Wrap-up, after-mixer, or final celebration.',
                            'imageUrl' => 'https://images.unsplash.com/photo-1496337589254-7e19d01cec44?auto=format&fit=crop&w=900&q=80',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pricingPlansPayload(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->get()
            ->map(function (Plan $plan): array {
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
                ];
            })
            ->values()
            ->all();
    }
}
