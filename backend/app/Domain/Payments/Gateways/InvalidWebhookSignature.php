<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Shared\Support\ApiException;

final class InvalidWebhookSignature extends ApiException
{
    public function __construct(string $message = 'Invalid webhook signature.')
    {
        parent::__construct('invalid_signature', $message, 400);
    }
}
