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
            ->orderByDesc('is_default')
            ->orderBy('price_cents')
            ->orderBy('currency')
            ->get()
            ->map(function (Plan $plan): array {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'currency' => $plan->currency,
                    'priceLabel' => $this->moneyLabel($plan->currency, (int) $plan->price_cents),
                    'storageLabel' => $this->storageLabel((int) $plan->storage_limit_bytes),
                    'uploadLabel' => number_format((int) $plan->upload_limit).' uploads',
                    'retentionLabel' => number_format((int) $plan->retention_days).' day retention',
                    'videoLabel' => number_format((int) $plan->video_max_duration_seconds).' sec video',
                    'photoSizeLabel' => number_format((int) round($plan->photo_max_size_bytes / 1048576)).' MB photo max',
                    'videoSizeLabel' => number_format((int) round($plan->video_max_size_bytes / 1048576)).' MB video max',
                    'isDefault' => $plan->is_default,
                    'ctaHref' => '/weddings',
                    'ctaLabel' => $plan->is_default ? 'Start with this plan' : 'See event flow',
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

    private function storageLabel(int $bytes): string
    {
        $gigabytes = $bytes / 1073741824;
        $formattedValue = fmod($gigabytes, 1.0) === 0.0
            ? number_format($gigabytes, 0)
            : number_format($gigabytes, 1);

        return $formattedValue.' GB storage';
    }
}
