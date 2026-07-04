<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessOnboardingRequest;
use App\Http\Requests\StoreBusinessWalletCheckoutRequest;
use App\Models\BusinessWalletTransaction;
use App\Models\Event;
use App\Support\BusinessWalletManager;
use App\Support\StripeCheckoutGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function dashboard(Request $request, BusinessWalletManager $walletManager): Response|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null && $user->isBusinessAccount(), 403);

        if (! $user->hasCompletedBusinessOnboarding()) {
            return to_route('dashboard.business.onboarding');
        }

        $profile = is_array($user->business_profile) ? $user->business_profile : [];

        $transactions = $user->businessWalletTransactions()
            ->latest('id')
            ->limit(25)
            ->get()
            ->map(fn (BusinessWalletTransaction $tx): array => [
                'id' => $tx->id,
                'kind' => (string) $tx->kind,
                'credits' => (int) $tx->credits,
                'description' => (string) $tx->description,
                'createdAt' => $tx->created_at?->toIso8601String(),
            ])->values()->all();

        $ownedEvents = $user->events()
            ->with('plan:id,name')
            ->withCount('assets')
            ->latest('id')
            ->get();

        $events = $ownedEvents
            ->take(20)
            ->map(fn (Event $event): array => [
                'id' => $event->id,
                'name' => $event->name,
                'status' => (string) $event->status,
                'planName' => $event->plan?->name ?? 'Custom plan',
                'assetCount' => (int) ($event->assets_count ?? 0),
                'links' => ['open' => route('events.show', $event)],
            ])->values()->all();

        $stats = [
            'events' => $ownedEvents->count(),
            'liveEvents' => $ownedEvents->where('status', Event::STATUS_LIVE)->count(),
            'uploads' => (int) $ownedEvents->sum('assets_count'),
            'credits' => (int) ($user->business_wallet_credits ?? 0),
        ];

        return Inertia::render('business/Dashboard', [
            'profile' => [
                'companyName' => (string) ($profile['company_name'] ?? ''),
                'brandName' => (string) ($profile['brand_name'] ?? ''),
                'billingEmail' => (string) ($profile['billing_email'] ?? $user->email),
                'logoUrl' => $this->businessLogoUrl($profile),
            ],
            'wallet' => [
                'credits' => (int) ($user->business_wallet_credits ?? 0),
                'currency' => (string) ($user->business_wallet_currency ?? 'EUR'),
            ],
            'stats' => $stats,
            'topUpPacks' => $walletManager->topUpPacks(),
            'currencies' => (array) config('business.supported_checkout_currencies', ['EUR']),
            'transactions' => $transactions,
            'events' => $events,
            'walletCheckoutUrl' => route('dashboard.business.wallet.checkout'),
            'editProfileUrl' => route('dashboard.business.onboarding'),
            'createEventUrl' => route('onboarding.create', ['restart' => 1]),
            'checkoutResult' => (string) $request->string('wallet_checkout'),
        ]);
    }

    public function activate(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if (! $user->isBusinessAccount()) {
            return to_route('businesses')->with(
                'info',
                'Business accounts now use a separate registration flow.',
            );
        }

        return $user->hasCompletedBusinessOnboarding()
            ? to_route('dashboard.business')
            : to_route('dashboard.business.onboarding');
    }

    public function onboarding(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null && $user->isBusinessAccount(), 403);

        if ($user->hasCompletedBusinessOnboarding()) {
            return to_route('dashboard.business');
        }

        $profile = is_array($user->business_profile) ? $user->business_profile : [];

        return Inertia::render('business/Onboarding', [
            'profile' => [
                'companyName' => (string) ($profile['company_name'] ?? ''),
                'brandName' => (string) ($profile['brand_name'] ?? ''),
                'billingEmail' => (string) ($profile['billing_email'] ?? $user->email),
                'phone' => (string) ($profile['phone'] ?? ''),
                'website' => (string) ($profile['website'] ?? ''),
                'primaryColor' => (string) ($profile['primary_color'] ?? '#171411'),
                'accentColor' => (string) ($profile['accent_color'] ?? '#D97706'),
                'logoUrl' => $this->businessLogoUrl($profile),
            ],
            'submitUrl' => route('dashboard.business.onboarding.store'),
            'cancelUrl' => route('dashboard.business.onboarding.cancel'),
        ]);
    }

    public function cancelOnboarding(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if ($user->hasCompletedBusinessOnboarding()) {
            return to_route('dashboard.business');
        }

        return to_route('businesses')->with('info', 'You can finish your business profile whenever you are ready.');
    }

    public function storeOnboarding(StoreBusinessOnboardingRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validated();
        $profile = is_array($user->business_profile) ? $user->business_profile : [];

        if ($request->hasFile('logo_file')) {
            $logoPath = $request->file('logo_file')->store("business/{$user->id}/branding", 'public');
            $profile['logo_disk'] = 'public';
            $profile['logo_path'] = $logoPath;
        }

        $profile = array_merge($profile, [
            'company_name' => $validated['company_name'],
            'brand_name' => $validated['brand_name'],
            'billing_email' => $validated['billing_email'],
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'primary_color' => $validated['primary_color'] ?? '#171411',
            'accent_color' => $validated['accent_color'] ?? '#D97706',
        ]);

        $user->forceFill([
            'business_profile' => $profile,
            'business_onboarded_at' => now(),
        ])->save();

        $request->session()->forget('business_onboarding_return_to');

        return to_route('dashboard.business')->with('success', 'Business profile saved.');
    }

    public function createWalletCheckout(
        StoreBusinessWalletCheckoutRequest $request,
        StripeCheckoutGateway $stripeCheckoutGateway,
        BusinessWalletManager $businessWalletManager,
    ): RedirectResponse|HttpResponse {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if (! $stripeCheckoutGateway->isConfigured()) {
            return back()->with('error', 'Online payments are not configured yet.');
        }

        $purchase = $businessWalletManager->createPurchaseIntent(
            $user,
            (int) $request->integer('credits'),
            (string) $request->string('currency'),
        );

        $successUrl = route('dashboard.business', [
            'wallet_checkout' => 'success',
        ]).'&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('dashboard.business', [
            'wallet_checkout' => 'cancelled',
        ]);

        $session = $stripeCheckoutGateway->createCheckoutSession([
            'mode' => 'payment',
            'client_reference_id' => (string) $purchase->id,
            'customer_email' => (string) (($user->business_profile['billing_email'] ?? null) ?: $user->email),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'payment_method_types' => ['card'],
            'metadata' => [
                'scope' => 'business_wallet',
                'purchase_id' => (string) $purchase->id,
                'user_id' => (string) $user->id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'scope' => 'business_wallet',
                    'purchase_id' => (string) $purchase->id,
                    'user_id' => (string) $user->id,
                ],
            ],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower((string) $purchase->checkout_currency),
                    'unit_amount' => (int) $purchase->localized_amount_cents,
                    'product_data' => [
                        'name' => "{$purchase->credits_purchased} business credits",
                        'description' => 'EventSmart business wallet top-up',
                    ],
                ],
            ]],
        ]);

        $purchase->forceFill([
            'stripe_checkout_session_id' => $session['id'],
        ])->save();

        return Inertia::location($session['url']);
    }

    private function businessLogoUrl(array $profile): ?string
    {
        $disk = $profile['logo_disk'] ?? null;
        $path = $profile['logo_path'] ?? null;

        if (! is_string($disk) || ! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }
}
