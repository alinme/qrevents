<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Plan;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class EventBillingManager
{
    public function applyAdminUpdate(
        Event $event,
        Plan $plan,
        bool $isPaid,
        ?CarbonImmutable $paymentDueAt,
        ?CarbonImmutable $paidAt,
        ?string $billingNote,
    ): Event {
        $timezone = $event->timezone ?: config('events.default_timezone', 'UTC');
        $windows = EventLifecycleWindows::build(
            $event->event_date?->toDateString(),
            $timezone,
            (int) $plan->upload_window_days,
            (int) $plan->grace_days,
        );
        $retentionEndsAt = $isPaid
            ? $this->resolveRetentionEndsAt($event, $plan, $windows['upload_window_ends_at'])
            : null;

        $event->update([
            'plan_id' => $plan->id,
            'currency' => $plan->currency,
            'is_paid' => $isPaid,
            'payment_due_at' => $paymentDueAt,
            'paid_at' => $paidAt,
            'billing_note' => $billingNote !== '' ? $billingNote : null,
            'retention_ends_at' => $retentionEndsAt,
            'renewal_grace_ends_at' => null,
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
            'video_max_duration_seconds' => $plan->video_max_duration_seconds,
            'photo_max_size_bytes' => $plan->photo_max_size_bytes,
            'video_max_size_bytes' => $plan->video_max_size_bytes,
            'moderation_enabled' => $plan->moderation_tools_enabled
                ? $event->moderation_enabled
                : false,
            'auto_moderation_enabled' => $plan->moderation_tools_enabled
                ? $event->auto_moderation_enabled
                : false,
            'stripe_checkout_session_id' => $isPaid ? $event->stripe_checkout_session_id : null,
            'stripe_payment_intent_id' => $isPaid ? $event->stripe_payment_intent_id : null,
            'stripe_paid_amount_cents' => $isPaid ? $event->stripe_paid_amount_cents : null,
            'stripe_paid_currency' => $isPaid ? $event->stripe_paid_currency : null,
            'status' => $this->resolveLifecycleStatus(
                $event,
                $isPaid,
                $paymentDueAt,
                $retentionEndsAt,
            ),
        ]);

        return $event->refresh();
    }

    public function applyStripeCheckoutPayment(
        Event $event,
        Plan $plan,
        string $checkoutSessionId,
        ?string $paymentIntentId,
        int $amountTotal,
        string $currency,
    ): Event {
        $timezone = $event->timezone ?: config('events.default_timezone', 'UTC');
        $windows = EventLifecycleWindows::build(
            $event->event_date?->toDateString(),
            $timezone,
            (int) $plan->upload_window_days,
            (int) $plan->grace_days,
        );
        $paidAt = $event->paid_at?->toImmutable()
            ?? now($timezone)->toImmutable();
        $retentionEndsAt = $this->resolveRetentionEndsAt($event, $plan, $windows['upload_window_ends_at']);

        $event->update([
            'plan_id' => $plan->id,
            'currency' => strtoupper($currency),
            'is_paid' => true,
            'payment_due_at' => null,
            'paid_at' => $paidAt,
            'retention_ends_at' => $retentionEndsAt,
            'renewal_grace_ends_at' => null,
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
            'video_max_duration_seconds' => $plan->video_max_duration_seconds,
            'photo_max_size_bytes' => $plan->photo_max_size_bytes,
            'video_max_size_bytes' => $plan->video_max_size_bytes,
            'moderation_enabled' => $plan->moderation_tools_enabled
                ? $event->moderation_enabled
                : false,
            'auto_moderation_enabled' => $plan->moderation_tools_enabled
                ? $event->auto_moderation_enabled
                : false,
            'stripe_checkout_session_id' => $checkoutSessionId,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_paid_amount_cents' => $amountTotal,
            'stripe_paid_currency' => strtoupper($currency),
            'status' => $this->resolveLifecycleStatus(
                $event,
                true,
                $event->payment_due_at,
                $retentionEndsAt,
            ),
        ]);

        return $event->refresh();
    }

    public function resolveRetentionEndsAt(Event $event, Plan $plan, ?CarbonImmutable $uploadWindowEndsAt = null): ?CarbonImmutable
    {
        $retentionDays = max(0, (int) $plan->retention_days);

        if ($retentionDays === 0) {
            return null;
        }

        $timezone = $event->timezone ?: config('events.default_timezone', 'UTC');
        $baseDate = $uploadWindowEndsAt
            ?? $event->upload_window_ends_at?->toImmutable()
            ?? ($event->event_date !== null
                ? CarbonImmutable::parse($event->event_date->toDateString(), $timezone)->endOfDay()
                : now($timezone)->toImmutable()->endOfDay());

        return $baseDate->addDays($retentionDays)->endOfDay();
    }

    public function resolveLifecycleStatus(
        Event $event,
        bool $isPaid,
        ?CarbonInterface $paymentDueAt,
        ?CarbonInterface $retentionEndsAt,
    ): string {
        if ($event->upload_window_starts_at === null || $event->upload_window_ends_at === null) {
            return Event::STATUS_DRAFT;
        }

        $now = now($event->timezone ?: config('events.default_timezone', 'UTC'));

        if ($now->lt($event->upload_window_starts_at)) {
            return Event::STATUS_SCHEDULED;
        }

        if ($now->betweenIncluded($event->upload_window_starts_at, $event->upload_window_ends_at)) {
            return Event::STATUS_LIVE;
        }

        if (! $isPaid && $paymentDueAt !== null && $now->gt($paymentDueAt)) {
            return Event::STATUS_LOCKED;
        }

        if ($isPaid && $retentionEndsAt !== null && $now->gt($retentionEndsAt)) {
            return Event::STATUS_EXPIRED;
        }

        return Event::STATUS_GRACE;
    }
}
