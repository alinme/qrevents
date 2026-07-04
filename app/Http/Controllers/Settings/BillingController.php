<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        $events = $user->events()
            ->with('plan:id,name,currency,price_cents')
            ->latest('id')
            ->get()
            ->map(function (Event $event): array {
                [$statusLabel, $statusTone] = $this->billingStatus($event);

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'planName' => $event->plan?->name ?? 'Custom plan',
                    'amountLabel' => $this->amountLabel($event),
                    'statusLabel' => $statusLabel,
                    'statusTone' => $statusTone,
                    'isPaid' => (bool) $event->is_paid,
                    'paidAt' => $event->paid_at?->toIso8601String(),
                    'dueAt' => $event->payment_due_at?->toIso8601String(),
                    'links' => [
                        'manage' => route('events.settings', $event),
                    ],
                ];
            })
            ->values()
            ->all();

        return Inertia::render('settings/Billing', [
            'events' => $events,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function billingStatus(Event $event): array
    {
        if ($event->is_paid) {
            return ['Paid', 'emerald'];
        }

        $dueAt = $event->payment_due_at;
        if ($dueAt !== null && $dueAt->isPast()) {
            return ['Overdue', 'rose'];
        }

        if ($dueAt !== null) {
            return ['Due soon', 'amber'];
        }

        return ['Not paid', 'zinc'];
    }

    private function amountLabel(Event $event): string
    {
        $plan = $event->plan;

        if ($plan === null) {
            return '—';
        }

        $currency = strtoupper((string) ($event->currency ?: $plan->currency));

        return sprintf('%s %.2f', $currency, ((int) $plan->price_cents) / 100);
    }
}
