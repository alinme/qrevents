<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Support\BusinessPlanCatalog;
use App\Support\BusinessWalletManager;
use App\Support\ExchangeRateManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class MarketingController extends Controller
{
    public function __construct(
        private BusinessPlanCatalog $businessPlanCatalog,
    ) {}

    public function pricing(): Response
    {
        return Inertia::render('Pricing', [
            'canRegister' => Features::enabled(Features::registration()),
            'plans' => $this->pricingPlans(),
            'businessTeaser' => [
                'href' => route('businesses'),
            ],
        ]);
    }

    public function businesses(
        Request $request,
        BusinessWalletManager $businessWalletManager,
        ExchangeRateManager $exchangeRateManager,
    ): Response {
        $user = $request->user();

        return Inertia::render('Businesses', [
            'canRegister' => Features::enabled(Features::registration()),
            'businessPacks' => $this->businessTopUpPacks($businessWalletManager, $exchangeRateManager),
            'businessPlans' => $this->businessPlans(),
            'supportedCheckoutCurrencies' => $exchangeRateManager->supportedCheckoutCurrencies(),
            'activateUrl' => $user !== null && ! $user->isBusinessAccount() ? route('dashboard.business.activate') : null,
            'onboardingUrl' => $user !== null && $user->isBusinessAccount() && ! $user->hasCompletedBusinessOnboarding()
                ? route('dashboard.business.onboarding')
                : null,
            'topUpUrl' => $user !== null && $user->isBusinessAccount() && $user->hasCompletedBusinessOnboarding()
                ? route('dashboard.business.wallet.checkout')
                : null,
            'dashboardUrl' => $user !== null && $user->canAccessBusinessDashboard()
                ? route('dashboard.business')
                : null,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pricingPlans(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->get()
            ->map(function (Plan $plan): array {
                $translationPrefix = "marketing.pricing.catalog.{$plan->slug}";

                return [
                    'id' => $plan->id,
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'summary' => __("$translationPrefix.summary"),
                    'description' => __("$translationPrefix.description"),
                    'currency' => $plan->currency,
                    'priceLabel' => $this->moneyLabel($plan->currency, (int) $plan->price_cents),
                    'billingLabel' => __("$translationPrefix.billing"),
                    'uploadLabel' => (int) $plan->upload_limit >= 1000000
                        ? __('marketing.pricing.common.unlimited_uploads')
                        : __('marketing.pricing.common.upload_limit', ['count' => number_format((int) $plan->upload_limit)]),
                    'retentionLabel' => __('marketing.pricing.common.retention_days', ['count' => number_format((int) $plan->retention_days)]),
                    'activeWindowLabel' => __('marketing.pricing.common.active_window_days', ['count' => number_format((int) $plan->upload_window_days)]),
                    'customizationLabel' => __("$translationPrefix.customization"),
                    'qualityLabel' => __("$translationPrefix.quality"),
                    'isHighlighted' => $plan->slug === 'plus',
                    'isDefault' => $plan->is_default,
                    'featureItems' => [
                        [
                            'label' => __("$translationPrefix.features.uploads.label"),
                            'help' => __("$translationPrefix.features.uploads.help"),
                        ],
                        [
                            'label' => __("$translationPrefix.features.guests.label"),
                            'help' => __("$translationPrefix.features.guests.help"),
                        ],
                        [
                            'label' => __("$translationPrefix.features.retention.label"),
                            'help' => __("$translationPrefix.features.retention.help"),
                        ],
                        [
                            'label' => __("$translationPrefix.features.customization.label"),
                            'help' => __("$translationPrefix.features.customization.help"),
                        ],
                        [
                            'label' => __("$translationPrefix.features.active_window.label"),
                            'help' => __("$translationPrefix.features.active_window.help"),
                        ],
                        [
                            'label' => __("$translationPrefix.features.quality.label"),
                            'help' => __("$translationPrefix.features.quality.help"),
                        ],
                        [
                            'label' => __("$translationPrefix.features.download_all.label"),
                            'help' => __("$translationPrefix.features.download_all.help"),
                            'available' => (bool) $plan->download_all_enabled,
                        ],
                        [
                            'label' => __("$translationPrefix.features.moderation.label"),
                            'help' => __("$translationPrefix.features.moderation.help"),
                            'available' => (bool) $plan->moderation_tools_enabled,
                        ],
                    ],
                    'ctaHref' => route('onboarding.create', ['plan' => $plan->slug]),
                    'ctaLabel' => __('marketing.actions.create_event'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function businessPlans(): array
    {
        return $this->businessPlanCatalog
            ->query()
            ->get()
            ->map(fn (Plan $plan): array => [
                'slug' => $plan->slug,
                'name' => $plan->name,
                'businessCreditCost' => (int) ($plan->business_credit_cost ?? 0),
                'consumerPriceLabel' => $this->moneyLabel($plan->currency, (int) $plan->price_cents),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function businessTopUpPacks(
        BusinessWalletManager $businessWalletManager,
        ExchangeRateManager $exchangeRateManager,
    ): array {
        return collect($businessWalletManager->topUpPacks())
            ->map(function (array $pack) use ($exchangeRateManager): array {
                $baseAmountCents = ((int) $pack['credits']) * 100;
                $priceLabels = collect($exchangeRateManager->supportedCheckoutCurrencies())
                    ->mapWithKeys(function (string $currency) use ($exchangeRateManager, $baseAmountCents): array {
                        $localizedAmountCents = $exchangeRateManager->convertEuroCentsToCurrencyCents(
                            $baseAmountCents,
                            $currency,
                        );

                        return [
                            $currency => $this->moneyLabel($currency, $localizedAmountCents),
                        ];
                    })
                    ->all();

                return [
                    'credits' => (int) $pack['credits'],
                    'bonus_percent' => (int) $pack['bonus_percent'],
                    'bonus_credits' => (int) $pack['bonus_credits'],
                    'total_credits' => (int) $pack['total_credits'],
                    'priceLabels' => $priceLabels,
                ];
            })
            ->values()
            ->all();
    }

    private function moneyLabel(string $currency, int $priceCents): string
    {
        if ($priceCents === 0) {
            return 'Free';
        }

        return sprintf('%s %.2f', strtoupper($currency), $priceCents / 100);
    }
}
