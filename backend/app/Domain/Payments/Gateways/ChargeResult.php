<?php

namespace App\Domain\Payments\Gateways;

final readonly class ChargeResult
{
    /**
     * @param  'succeeded'|'failed'|'pending'  $status
     */
    public function __construct(
        public string $status,
        public ?string $paymentId = null,
        public ?string $failureReason = null,
        public ?string $method = null,
    ) {}

    public function succeeded(): bool
    {
        return $this->status === 'succeeded';
    }
}
