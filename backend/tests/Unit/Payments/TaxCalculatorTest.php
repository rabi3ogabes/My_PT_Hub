<?php

use App\Domain\Payments\Services\TaxCalculator;

it('uses the seller country VAT rate', function (?string $country, float $rate) {
    expect(TaxCalculator::rateForCountry($country))->toBe($rate);
})->with([
    'Qatar' => ['QA', 0.0],
    'KSA' => ['SA', 15.0],
    'UAE' => ['AE', 5.0],
    'France' => ['FR', 20.0],
    'Morocco' => ['MA', 20.0],
    'Egypt' => ['EG', 14.0],
    'other' => ['US', 0.0],
    'unknown' => [null, 0.0],
]);

it('extracts tax from tax-inclusive prices', function (int $amount, float $rate, int $tax, int $net) {
    $calc = TaxCalculator::compute($amount, 0, $rate, true);
    expect($calc['total_minor'])->toBe($amount)
        ->and($calc['tax_minor'])->toBe($tax)
        ->and($calc['net_minor'])->toBe($net)
        ->and($calc['net_minor'] + $calc['tax_minor'])->toBe($calc['total_minor']);
})->with([
    'KSA 15% on SAR 115.00' => [11500, 15.0, 1500, 10000],
    'UAE 5% on AED 105.00' => [10500, 5.0, 500, 10000],
    'France 20% on EUR 120.00' => [12000, 20.0, 2000, 10000],
    'Egypt 14% on EGP 1,140.00' => [114000, 14.0, 14000, 100000],
    'Qatar 0% on QAR 399.00' => [39900, 0.0, 0, 39900],
    'rounding EUR 99.99 @20%' => [9999, 20.0, 1667, 8332],
]);

it('adds tax on top of tax-exclusive prices', function (int $amount, float $rate, int $tax, int $total) {
    $calc = TaxCalculator::compute($amount, 0, $rate, false);
    expect($calc['net_minor'])->toBe($amount)->and($calc['tax_minor'])->toBe($tax)->and($calc['total_minor'])->toBe($total);
})->with([
    'KSA' => [10000, 15.0, 1500, 11500],
    'Morocco' => [50000, 20.0, 10000, 60000],
    'Egypt rounding' => [999, 14.0, 140, 1139],
    'Qatar' => [39900, 0.0, 0, 39900],
]);

it('applies discounts before tax and never goes negative', function () {
    $calc = TaxCalculator::compute(20000, 5000, 20.0, false);
    expect($calc['discount_minor'])->toBe(5000)->and($calc['net_minor'])->toBe(15000)->and($calc['tax_minor'])->toBe(3000)->and($calc['total_minor'])->toBe(18000);

    $inclusive = TaxCalculator::compute(11500, 2300, 15.0, true);
    expect($inclusive['total_minor'])->toBe(9200)->and($inclusive['tax_minor'])->toBe(1200);

    $free = TaxCalculator::compute(1000, 5000, 15.0, true);
    expect($free['discount_minor'])->toBe(1000)->and($free['total_minor'])->toBe(0)->and($free['tax_minor'])->toBe(0);
});
