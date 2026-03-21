<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Plan;
use App\Support\EventBillingManager;
use App\Support\StripeCheckoutGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        StripeCheckoutGateway $stripeCheckoutGateway,
        EventBillingManager $eventBillingManager,
    ): JsonResponse
    {
        $signature = (string) $request->header('Stripe-Signature', '');

        if ($signature === '') {
            return response()->json(['message' => 'Missing Stripe signature.'], 400);
        }

        try {
            $event = $stripeCheckoutGateway->parseWebhookEvent(
                $request->getContent(),
                $signature,
            );
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            return response()->json(['message' => 'Invalid Stripe webhook payload.'], 400);
        } catch (RuntimeException $exception) {
            Log::warning('Stripe webhook rejected because configuration is missing.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Stripe is not configured.'], 503);
        }

        if (! in_array($event['type'], [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
        ], true)) {
            return response()->json(['received' => true]);
        }

        if (($event['paymentStatus'] ?? null) !== 'paid') {
            return response()->json(['received' => true]);
        }

        $metadata = $event['metadata'];
        $eventId = (int) ($metadata['event_id'] ?? 0);
        $planId = (int) ($metadata['plan_id'] ?? 0);

        $eventModel = Event::query()->find($eventId);
        $plan = Plan::query()->whereKey($planId)->where('is_active', true)->first();

        if (! $eventModel instanceof Event || ! $plan instanceof Plan) {
            Log::warning('Stripe checkout webhook referenced missing event or plan.', [
                'event_id' => $eventId,
                'plan_id' => $planId,
                'session_id' => $event['sessionId'],
            ]);

            return response()->json(['received' => true]);
        }

        $eventBillingManager->applyStripeCheckoutPayment(
            $eventModel,
            $plan,
            (string) ($event['sessionId'] ?? ''),
            $event['paymentIntentId'],
            (int) $event['amountTotal'],
            (string) ($event['currency'] ?? $plan->currency),
        );

        return response()->json(['received' => true]);
    }
}
