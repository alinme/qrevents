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

    /**
     * @return list<array{credits: int, bonus_percent: int, bonus_credits: int, total_credits: int}>
     */
    public function topUpPacks(): array
    {
        /** @var list<array{credits: int, bonus_percent: int}> $configuredPacks */
        $configuredPacks = config('business.top_up_packs', []);

        return array_map(function (array $pack): array {
            $credits = (int) ($pack['credits'] ?? 0);
            $bonusPercent = (int) ($pack['bonus_percent'] ?? 0);
            $bonusCredits = (int) floor($credits * ($bonusPercent / 100));

            return [
                'credits' => $credits,
                'bonus_percent' => $bonusPercent,
                'bonus_credits' => $bonusCredits,
                'total_credits' => $credits + $bonusCredits,
            ];
        }, $configuredPacks);
    }

    /**
     * @return array{credits: int, bonus_percent: int, bonus_credits: int, total_credits: int}
     */
    public function resolvePack(int $credits): array
    {
        foreach ($this->topUpPacks() as $pack) {
            if ($pack['credits'] === $credits) {
                return $pack;
            }
        }

        throw new RuntimeException('The selected business top-up pack is not available.');
    }

    public function createPurchaseIntent(User $user, int $credits, string $checkoutCurrency): BusinessWalletPurchase
    {
        $pack = $this->resolvePack($credits);
        $normalizedCheckoutCurrency = strtoupper(trim($checkoutCurrency));

        if (! in_array($normalizedCheckoutCurrency, $this->exchangeRateManager->supportedCheckoutCurrencies(), true)) {
            throw new RuntimeException('The selected checkout currency is not supported.');
        }

        $baseAmountCents = $pack['credits'] * 100;
        $lockedFxRate = $this->exchangeRateManager->latestRate($normalizedCheckoutCurrency);
        $localizedAmountCents = $this->exchangeRateManager->convertEuroCentsToCurrencyCents(
            $baseAmountCents,
            $normalizedCheckoutCurrency,
            $lockedFxRate,
        );

        return $user->businessWalletPurchases()->create([
            'credits_purchased' => $pack['credits'],
            'bonus_credits' => $pack['bonus_credits'],
            'total_credits' => $pack['total_credits'],
            'base_amount_cents' => $baseAmountCents,
            'checkout_currency' => $normalizedCheckoutCurrency,
            'localized_amount_cents' => $localizedAmountCents,
            'locked_fx_rate' => $lockedFxRate,
            'metadata' => [
                'bonus_percent' => $pack['bonus_percent'],
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
                $user->businessWalletTransactions()->create([
                    'purchase_id' => $lockedPurchase->id,
                    'kind' => 'bonus',
                    'credits' => (int) $lockedPurchase->bonus_credits,
                    'description' => 'Business top-up bonus credits',
                    'metadata' => [
                        'bonus_percent' => (int) (($lockedPurchase->metadata['bonus_percent'] ?? 0)),
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
