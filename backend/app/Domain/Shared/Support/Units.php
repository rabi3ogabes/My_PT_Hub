<?php

namespace App\Domain\Shared\Support;

/** Metric is stored; imperial is display-only. */
final class Units
{
    public const KG_PER_LB = 0.45359237;

    public const CM_PER_IN = 2.54;

    public static function kgToLb(float $kg): float
    {
        return round($kg / self::KG_PER_LB, 1);
    }

    public static function lbToKg(float $lb): float
    {
        return round($lb * self::KG_PER_LB, 2);
    }

    public static function cmToIn(float $cm): float
    {
        return round($cm / self::CM_PER_IN, 1);
    }

    public static function inToCm(float $in): float
    {
        return round($in * self::CM_PER_IN, 1);
    }

    public static function weight(float $kg, string $units): string
    {
        return $units === 'imperial' ? self::kgToLb($kg).' lb' : round($kg, 1).' kg';
    }
}
