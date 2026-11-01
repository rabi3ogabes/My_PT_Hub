<?php

namespace App\Domain\Workouts\Services;

/** Estimated one-rep max formulas. Inputs in kg; reps above 15 are not estimated. */
final class OneRepMax
{
    public const MAX_REPS = 15;

    /** Epley: w × (1 + r / 30); a single is the weight itself. */
    public static function epley(?float $weightKg, ?int $reps): ?float
    {
        if ($weightKg === null || $reps === null || $weightKg <= 0 || $reps < 1 || $reps > self::MAX_REPS) {
            return null;
        }

        return $reps === 1 ? round($weightKg, 2) : round($weightKg * (1 + $reps / 30), 2);
    }

    /** Brzycki: w × 36 / (37 − r). */
    public static function brzycki(?float $weightKg, ?int $reps): ?float
    {
        if ($weightKg === null || $reps === null || $weightKg <= 0 || $reps < 1 || $reps > self::MAX_REPS) {
            return null;
        }

        return $reps === 1 ? round($weightKg, 2) : round($weightKg * 36 / (37 - $reps), 2);
    }

    /** Weight for a percentage of a 1RM, rounded to the nearest increment. */
    public static function percentOf(float $oneRepMaxKg, float $percent, float $roundTo = 2.5): float
    {
        return self::round($oneRepMaxKg * $percent / 100, $roundTo);
    }

    public static function round(float $kg, float $roundTo = 2.5): float
    {
        if ($roundTo <= 0) {
            return round($kg, 2);
        }

        return round(round($kg / $roundTo) * $roundTo, 2);
    }
}
