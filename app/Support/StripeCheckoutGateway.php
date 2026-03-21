<?php

namespace App\Support;

use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeCheckoutGateway
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{id: string, url: string}
     */
    public function createCheckoutSession(array $payload): array
    {
        $session = $this->client()->checkout->sessions->create($payload);

        return [
            'id' => (string) $session->id,
            'url' => (string) $session->url,
        ];
    }

    /**
     * @return array{
     *   type: string,
     *   sessionId: string|null,
     *   paymentStatus: string|null,
     *   paymentIntentId: string|null,
     *   amountTotal: int,
     *   currency: string|null,
     *   metadata: array<string, string>
     * }
     *
     * @throws SignatureVerificationException
     * @throws UnexpectedValueException
     */
    public function parseWebhookEvent(string $payload, string $signature): array
    {
        $event = Webhook::constructEvent(
            $payload,
            $signature,
            $this->webhookSecret(),
        );

        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;
        $paymentIntentId = $session->payment_intent;

        return [
            'type' => (string) $event->type,
            'sessionId' => is_string($session->id) ? $session->id : null,
            'paymentStatus' => is_string($session->payment_status) ? $session->payment_status : null,
            'paymentIntentId' => is_string($paymentIntentId) ? $paymentIntentId : null,
            'amountTotal' => (int) ($session->amount_total ?? 0),
            'currency' => is_string($session->currency) ? $session->currency : null,
            'metadata' => $this->normalizeMetadata($session->metadata ?? []),
        ];
    }

    public function isConfigured(): bool
    {
        return trim((string) config('services.stripe.secret')) !== '';
    }

    private function client(): StripeClient
    {
        return new StripeClient($this->secretKey());
    }

    private function secretKey(): string
    {
        $secretKey = trim((string) config('services.stripe.secret'));

        if ($secretKey === '') {
            throw new RuntimeException('Stripe secret is not configured.');
        }

        return $secretKey;
    }

    private function webhookSecret(): string
    {
        $webhookSecret = trim((string) config('services.stripe.webhook_secret'));

        if ($webhookSecret === '') {
            throw new RuntimeException('Stripe webhook secret is not configured.');
        }

        return $webhookSecret;
    }

    /**
     * @return array<string, string>
     */
    private function normalizeMetadata(mixed $metadata): array
    {
        if ($metadata instanceof StripeObject) {
            $metadata = $metadata->toArray();
        } elseif ($metadata instanceof \Traversable) {
            $metadata = iterator_to_array($metadata);
        } elseif (is_object($metadata)) {
            $metadata = get_object_vars($metadata);
        }

        if (! is_array($metadata)) {
            return [];
        }

        $normalized = [];

        foreach ($metadata as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (is_scalar($value)) {
                $normalized[$key] = (string) $value;
            }
        }

        return $normalized;
    }
}
