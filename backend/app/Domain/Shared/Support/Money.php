<?php

namespace App\Domain\Shared\Support;

use NumberFormatter;

/** Money is always integer minor units + ISO 4217 currency. */
final class Money
{
    /** Currencies with 3 decimal minor units (Gulf dinars). */
    public const THREE_DECIMALS = ['KWD', 'BHD', 'OMR', 'JOD', 'TND', 'LYD', 'IQD'];

    public const ZERO_DECIMALS = ['JPY', 'KRW', 'XOF', 'XAF'];

    public static function decimals(string $currency): int
    {
        $currency = strtoupper($currency);

        return in_array($currency, self::THREE_DECIMALS, true) ? 3 : (in_array($currency, self::ZERO_DECIMALS, true) ? 0 : 2);
    }

    public static function toMajor(int $minor, string $currency): float
    {
        return $minor / (10 ** self::decimals($currency));
    }

    public static function toMinor(float|int|string $major, string $currency): int
    {
        return (int) round(((float) $major) * (10 ** self::decimals($currency)));
    }

    public static function format(int $minor, string $currency, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $fmt = new NumberFormatter($locale === 'ar' ? 'ar_QA' : $locale, NumberFormatter::CURRENCY);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, self::decimals($currency));
        $out = $fmt->formatCurrency(self::toMajor($minor, $currency), strtoupper($currency));

        return $out === false ? strtoupper($currency).' '.number_format(self::toMajor($minor, $currency), self::decimals($currency)) : $out;
    }
}
