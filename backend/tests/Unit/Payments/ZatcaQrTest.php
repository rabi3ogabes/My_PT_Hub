<?php

use App\Domain\Payments\Services\ZatcaQr;

it('encodes the five ZATCA TLV fields as base64', function () {
    $qr = ZatcaQr::encode('شركة التدريب', '310122393500003', '2026-11-01T09:00:00+00:00', '115.00', '15.00');
    $fields = ZatcaQr::decode($qr);

    expect($fields)->toBe([
        1 => 'شركة التدريب',
        2 => '310122393500003',
        3 => '2026-11-01T09:00:00+00:00',
        4 => '115.00',
        5 => '15.00',
    ]);
    // Tag 1 length counts UTF-8 bytes, not characters.
    $bytes = base64_decode($qr);
    expect(ord($bytes[0]))->toBe(1)->and(ord($bytes[1]))->toBe(strlen('شركة التدريب'));
});
