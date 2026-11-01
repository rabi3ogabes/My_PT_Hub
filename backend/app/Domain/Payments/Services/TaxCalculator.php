<?php

namespace App\Domain\Payments\Services;

use App\Domain\Tenancy\Models\Tenant;

/**
 * VAT / sales tax in integer minor units. Rates by seller country:
 * Qatar 0, KSA 15 (ZATCA), UAE 5, France 20 (TVA), Morocco 20, Egypt 14, otherwise 0.
 */
final class TaxCalculator
{
    public function rateFor(Tenant $tenant): float
    {
        if ($tenant->setting('payments.tax_enabled', true) === false) {
            return 0.0;
        }
        $override = $tenant->setting('payments.tax_rate');
        if (is_numeric($override)) {
            return (float) $override;
        }

        return self::rateForCountry($tenant->country);
    }

    public static function rateForCountry(?string $country): float
    {
        return (float) (config('payments.tax_rates')[strtoupper((string) $country)] ?? 0);
    }

    /**
     * @return array{subtotal_minor: int, discount_minor: int, net_minor: int, tax_minor: int, total_minor: int, tax_rate: float, tax_inclusive: bool}
     */
    public static function compute(int $amountMinor, int $discountMinor, float $rate, bool $inclusive): array
    {
        $discount = max(0, min($discountMinor, $amountMinor));
        $base = $amountMinor - $discount;

        if ($inclusive) {
            // Price already contains tax: extract it.
            $tax = $rate > 0 ? (int) round($base * $rate / (100 + $rate)) : 0;
            $net = $base - $tax;
            $total = $base;
        } else {
            $net = $base;
            $tax = $rate > 0 ? (int) round($net * $rate / 100) : 0;
            $total = $net + $tax;
        }

        return [
            'subtotal_minor' => $amountMinor,
            'discount_minor' => $discount,
            'net_minor' => $net,
            'tax_minor' => $tax,
            'total_minor' => $total,
            'tax_rate' => $rate,
            'tax_inclusive' => $inclusive,
        ];
    }
}
