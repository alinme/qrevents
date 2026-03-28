<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessOnboardingRequest;
use App\Http\Requests\StoreBusinessWalletCheckoutRequest;
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
    public function activate(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if (! $user->isBusinessAccount()) {
            $user->forceFill([
                'account_type' => $user::ACCOUNT_TYPE_BUSINESS,
            ])->save();
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
        ]);
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
