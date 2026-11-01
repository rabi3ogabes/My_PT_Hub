<?php

namespace App\Domain\Payments\Gateways;

final readonly class RefundResult
{
    /**
     * @param  'succeeded'|'pending'|'failed'  $status
     */
    public function __construct(
        public string $status,
        public ?string $refundId = null,
    ) {}
}
