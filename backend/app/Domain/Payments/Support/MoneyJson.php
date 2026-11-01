<?php

namespace App\Domain\Payments\Support;

use App\Domain\Shared\Support\Money;

final class MoneyJson
{
    /** @return array{amount_minor: int, currency: string, formatted: string} */
    public static function make(int $minor, string $currency): array
    {
        return ['amount_minor' => $minor, 'currency' => strtoupper($currency), 'formatted' => Money::format($minor, $currency)];
    }
}
