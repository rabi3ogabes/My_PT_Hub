<?php

namespace App\Domain\Payments\Gateways;

/**
 * A provider event normalised to what Aurum acts on.
 * Types: payment_succeeded | payment_failed | refunded | subscription_renewed |
 * subscription_payment_failed | subscription_cancelled | ignored.
 */
final readonly class WebhookResult
{
    public const SUCCEEDED = 'payment_succeeded';

    public const FAILED = 'payment_failed';

    public const REFUNDED = 'refunded';

    public const RENEWED = 'subscription_renewed';

    public const RENEWAL_FAILED = 'subscription_payment_failed';

    public const CANCELLED = 'subscription_cancelled';

    public const IGNORED = 'ignored';

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $externalId,
        public string $type,
        public ?string $orderId = null,
        public ?string $reference = null,
        public ?string $paymentId = null,
        public ?int $amountMinor = null,
        public ?string $currency = null,
        public ?string $subscriptionReference = null,
        public ?string $customerReference = null,
        public ?string $paymentMethodReference = null,
        public ?string $method = null,
        public ?string $failureReason = null,
        public array $payload = [],
    ) {}

    /** @param  array<string, mixed>  $payload */
    public static function ignored(string $externalId, array $payload = []): self
    {
        return new self($externalId, self::IGNORED, payload: $payload);
    }
}
