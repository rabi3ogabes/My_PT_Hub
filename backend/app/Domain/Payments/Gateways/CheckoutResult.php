<?php

namespace App\Domain\Payments\Gateways;

final readonly class CheckoutResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $url,
        public string $reference,
        public array $raw = [],
    ) {}
}
