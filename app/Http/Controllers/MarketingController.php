<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class MarketingController extends Controller
{
    public function pricing(): Response
    {
        return Inertia::render('Pricing', [
            'canRegister' => Features::enabled(Features::registration()),
            'plans' => $this->pricingPlans(),
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

    private function moneyLabel(string $currency, int $priceCents): string
    {
        if ($priceCents === 0) {
            return 'Free';
        }

        return sprintf('%s %.2f', strtoupper($currency), $priceCents / 100);
    }
}
