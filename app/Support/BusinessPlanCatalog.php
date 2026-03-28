<?php

namespace App\Support;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;

class BusinessPlanCatalog
{
    /**
     * @return array<string, array{business_enabled: bool, business_credit_cost: int|null}>
     */
    public function defaults(): array
    {
        return [
            'free' => [
                'business_enabled' => false,
                'business_credit_cost' => null,
            ],
            'plus' => [
                'business_enabled' => true,
                'business_credit_cost' => 25,
            ],
            'pro' => [
                'business_enabled' => true,
                'business_credit_cost' => 50,
            ],
        ];
    }

    public function ensureConfigured(): void
    {
        $existingPlans = Plan::query()
            ->whereIn('slug', array_keys($this->defaults()))
            ->get(['id', 'slug', 'business_enabled', 'business_credit_cost']);

        foreach ($existingPlans as $plan) {
            $expected = $this->defaults()[$plan->slug] ?? null;

            if ($expected === null) {
                continue;
            }

            $expectedCost = $expected['business_credit_cost'];
            $actualCost = $plan->business_credit_cost === null ? null : (int) $plan->business_credit_cost;

            if (
                (bool) $plan->business_enabled === $expected['business_enabled']
                && $actualCost === $expectedCost
            ) {
                continue;
            }

            $plan->forceFill([
                'business_enabled' => $expected['business_enabled'],
                'business_credit_cost' => $expectedCost,
            ])->save();
        }
    }

    public function query(): Builder
    {
        $this->ensureConfigured();

        return Plan::query()
            ->where('is_active', true)
            ->where('business_enabled', true)
            ->orderBy('price_cents');
    }
}
