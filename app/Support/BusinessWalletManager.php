<?php

namespace App\Support;

use App\Models\BusinessWalletPurchase;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BusinessWalletManager
{
    public function __construct(
        private ExchangeRateManager $exchangeRateManager,
    ) {}

    public function minTopUpEuros(): int
    {
        return max(1, (int) config('business.credit_min_topup_eur', 100));
    }

    public function topUpStepEuros(): int
    {
        return max(1, (int) config('business.credit_topup_step_eur', 50));
    }

    /**
     * Preset top-up amounts surfaced as tier cards.
     *
     * @return list<array{amount_eur: int, base_amount_cents: int, price_per_credit_cents: int, discount_percent: int, credits_purchased: int, bonus_credits: int, total_credits: int}>
     */
    public function presets(): array
    {
        /** @var list<int> $presets */
        $presets = config('business.credit_presets_eur', []);

        return array_map(fn (int $eur): array => $this->quoteTopUp($eur), $presets);
    }

    /**
     * Quote a wholesale top-up: the volume tier for the prepaid amount sets the
     * per-credit price; the discount below face value is stored as bonus credits
     * so the existing purchase/transaction machinery is unchanged.
     *
     * @return array{amount_eur: int, base_amount_cents: int, price_per_credit_cents: int, discount_percent: int, credits_purchased: int, bonus_credits: int, total_credits: int}
     */
    public function quoteTopUp(int $euros): array
    {
        $euros = $this->normalizeTopUpAmount($euros);
        $tier = $this->tierForAmount($euros);

        $pricePerCreditCents = max(1, (int) $tier['price_per_credit_cents']);
        $totalCredits = intdiv($euros * 100, $pricePerCreditCents);
        $faceCredits = $euros; // 1 credit spends like €1 on events
        $bonusCredits = max(0, $totalCredits - $faceCredits);

        return [
            'amount_eur' => $euros,
            'base_amount_cents' => $euros * 100,
            'price_per_credit_cents' => $pricePerCreditCents,
            'discount_percent' => (int) $tier['discount_percent'],
            'credits_purchased' => $faceCredits,
            'bonus_credits' => $bonusCredits,
            'total_credits' => $totalCredits,
        ];
    }

    private function normalizeTopUpAmount(int $euros): int
    {
        $min = $this->minTopUpEuros();
        $step = $this->topUpStepEuros();

        if ($euros < $min) {
            throw new RuntimeException("The minimum wallet top-up is €{$min}.");
        }

        if ((($euros - $min) % $step) !== 0) {
            throw new RuntimeException("Wallet top-ups must be in €{$step} increments.");
        }

        return $euros;
    }

    /**
     * @return array{min_eur: int, price_per_credit_cents: int, discount_percent: int}
     */
    private function tierForAmount(int $euros): array
    {
        /** @var list<array{min_eur: int, price_per_credit_cents: int, discount_percent: int}> $tiers */
        $tiers = config('business.credit_tiers', []);

        $match = null;
        foreach ($tiers as $tier) {
            if ($euros >= (int) $tier['min_eur']) {
                $match = $tier; // tiers ascend, so the last match is the best applicable
            }
        }

        if ($match === null) {
            throw new RuntimeException('No credit tier is available for this amount.');
        }

        return $match;
    }

    public function createPurchaseIntent(User $user, int $amountEuros, string $checkoutCurrency): BusinessWalletPurchase
    {
        $quote = $this->quoteTopUp($amountEuros);
        $normalizedCheckoutCurrency = strtoupper(trim($checkoutCurrency));

        if (! in_array($normalizedCheckoutCurrency, $this->exchangeRateManager->supportedCheckoutCurrencies(), true)) {
            throw new RuntimeException('The selected checkout currency is not supported.');
        }

        $baseAmountCents = $quote['base_amount_cents'];
        $lockedFxRate = $this->exchangeRateManager->latestRate($normalizedCheckoutCurrency);
        $localizedAmountCents = $this->exchangeRateManager->convertEuroCentsToCurrencyCents(
            $baseAmountCents,
            $normalizedCheckoutCurrency,
            $lockedFxRate,
        );

        return $user->businessWalletPurchases()->create([
            'credits_purchased' => $quote['credits_purchased'],
            'bonus_credits' => $quote['bonus_credits'],
            'total_credits' => $quote['total_credits'],
            'base_amount_cents' => $baseAmountCents,
            'checkout_currency' => $normalizedCheckoutCurrency,
            'localized_amount_cents' => $localizedAmountCents,
            'locked_fx_rate' => $lockedFxRate,
            'metadata' => [
                'discount_percent' => $quote['discount_percent'],
                'price_per_credit_cents' => $quote['price_per_credit_cents'],
                'purchase_token' => Str::uuid()->toString(),
            ],
        ]);
    }

    public function applyPurchasePayment(
        BusinessWalletPurchase $purchase,
        string $checkoutSessionId,
        ?string $paymentIntentId,
    ): BusinessWalletPurchase {
        return DB::transaction(function () use ($purchase, $checkoutSessionId, $paymentIntentId): BusinessWalletPurchase {
            /** @var BusinessWalletPurchase $lockedPurchase */
            $lockedPurchase = BusinessWalletPurchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPurchase->status === 'paid') {
                return $lockedPurchase;
            }

            /** @var User $user */
            $user = User::query()
                ->whereKey($lockedPurchase->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $user->forceFill([
                'business_wallet_credits' => (int) $user->business_wallet_credits + (int) $lockedPurchase->total_credits,
                'business_wallet_currency' => $this->exchangeRateManager->baseCurrency(),
            ])->save();

            $lockedPurchase->forceFill([
                'status' => 'paid',
                'stripe_checkout_session_id' => $checkoutSessionId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'paid_at' => now(),
            ])->save();

            $user->businessWalletTransactions()->create([
                'purchase_id' => $lockedPurchase->id,
                'kind' => 'top_up',
                'credits' => (int) $lockedPurchase->credits_purchased,
                'description' => 'Business wallet top-up',
                'metadata' => [
                    'checkout_currency' => $lockedPurchase->checkout_currency,
                    'base_amount_cents' => $lockedPurchase->base_amount_cents,
                    'localized_amount_cents' => $lockedPurchase->localized_amount_cents,
                    'locked_fx_rate' => $lockedPurchase->locked_fx_rate,
                ],
            ]);

            if ((int) $lockedPurchase->bonus_credits > 0) {
                $discountPercent = (int) ($lockedPurchase->metadata['discount_percent'] ?? 0);
                $user->businessWalletTransactions()->create([
                    'purchase_id' => $lockedPurchase->id,
                    'kind' => 'bonus',
                    'credits' => (int) $lockedPurchase->bonus_credits,
                    'description' => $discountPercent > 0
                        ? "Wholesale discount credits ({$discountPercent}% off)"
                        : 'Wholesale discount credits',
                    'metadata' => [
                        'discount_percent' => $discountPercent,
                    ],
                ]);
            }

            return $lockedPurchase->refresh();
        });
    }

    public function canAffordPlan(User $user, Plan $plan): bool
    {
        return (int) $user->business_wallet_credits >= $this->planCreditCost($plan);
    }

    public function planCreditCost(Plan $plan): int
    {
        $cost = (int) ($plan->business_credit_cost ?? 0);

        if (! $plan->business_enabled || $cost <= 0) {
            throw new RuntimeException('This event plan is not available for business wallet payments.');
        }

        return $cost;
    }

    public function debitForEvent(User $user, Event $event, Plan $plan): void
    {
        DB::transaction(function () use ($user, $event, $plan): void {
            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $cost = $this->planCreditCost($plan);

            if ((int) $lockedUser->business_wallet_credits < $cost) {
                throw new RuntimeException('Not enough business credits to create this event.');
            }

            $lockedUser->forceFill([
                'business_wallet_credits' => (int) $lockedUser->business_wallet_credits - $cost,
            ])->save();

            $lockedUser->businessWalletTransactions()->create([
                'event_id' => $event->id,
                'kind' => 'event_debit',
                'credits' => -$cost,
                'description' => "Created {$plan->name} event: {$event->name}",
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                    'event_id' => $event->id,
                ],
            ]);
        });
    }
}
