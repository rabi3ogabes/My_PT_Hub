<?php

namespace App\Domain\Payments\Services;

/**
 * ZATCA (Saudi e-invoicing) simplified-invoice QR: base64 of TLV fields
 * 1 seller name, 2 VAT registration number, 3 timestamp (ISO 8601), 4 total incl. VAT, 5 VAT amount.
 */
final class ZatcaQr
{
    public static function encode(string $sellerName, string $vatNumber, string $timestamp, string $totalWithVat, string $vatAmount): string
    {
        $tlv = '';
        foreach ([1 => $sellerName, 2 => $vatNumber, 3 => $timestamp, 4 => $totalWithVat, 5 => $vatAmount] as $tag => $value) {
            $tlv .= chr($tag).chr(strlen($value)).$value;
        }

        return base64_encode($tlv);
    }

    /** @return array<int, string> */
    public static function decode(string $base64): array
    {
        $bytes = base64_decode($base64, true) ?: '';
        $out = [];
        $i = 0;
        while ($i + 2 <= strlen($bytes)) {
            $tag = ord($bytes[$i]);
            $len = ord($bytes[$i + 1]);
            $out[$tag] = substr($bytes, $i + 2, $len);
            $i += 2 + $len;
        }

        return $out;
    }
}
