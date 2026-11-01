<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Shared\Support\ApiException;

/**
 * Fasting windows for meal plans.
 *
 * - type "fasting": intermittent window from plan.fasting {window_start, window_end}.
 * - type "ramadan": eating window from iftar (Maghrib) to suhoor end (next Fajr)
 *   for plan.fasting {city} or {lat, lng, timezone}; meal slots are shifted:
 *   breakfast → suhoor (30 min before Fajr), lunch → iftar (Maghrib),
 *   dinner → 2.5 h after iftar, snacks → midway through the night.
 */
final class FastingService
{
    public function __construct(private readonly SolarCalculator $solar) {}

    /**
     * Resolve a location from {city} or {lat, lng, timezone}.
     *
     * @param  array<string, mixed>  $settings
     * @return array{lat: float, lng: float, timezone: string, city: string|null}
     */
    public function location(array $settings): array
    {
        if (! empty($settings['city'])) {
            $city = SolarCalculator::city((string) $settings['city'])
                ?? throw ApiException::invalid('unknown_city', __('nutrition.errors.unknown_city'), ['fasting.city' => [__('nutrition.errors.unknown_city')]]);

            return $city + ['city' => strtolower((string) $settings['city'])];
        }
        if (isset($settings['lat'], $settings['lng'])) {
            return [
                'lat' => (float) $settings['lat'],
                'lng' => (float) $settings['lng'],
                'timezone' => (string) ($settings['timezone'] ?? 'UTC'),
                'city' => null,
            ];
        }

        throw ApiException::invalid('unknown_city', __('nutrition.errors.unknown_city'), ['fasting.city' => [__('nutrition.errors.unknown_city')]]);
    }

    /**
     * Ramadan times for a date and location (local HH:MM).
     *
     * @param  array<string, mixed>  $settings
     * @return array{date: string, timezone: string, suhoor_end: string, iftar: string, next_suhoor_end: string, suhoor_meal: string, iftar_meal: string, dinner_meal: string, snack_meal: string, fasting_minutes: int}
     */
    public function ramadanTimes(string $date, array $settings): array
    {
        $loc = $this->location($settings);
        $w = $this->solar->fastingWindow($date, $loc['lat'], $loc['lng'], $loc['timezone']);
        $nightMinutes = (int) $w['iftar']->diffInMinutes($w['next_suhoor_end']);

        return [
            'date' => $date,
            'timezone' => $loc['timezone'],
            'suhoor_end' => $w['suhoor_end']->format('H:i'),
            'iftar' => $w['iftar']->format('H:i'),
            'next_suhoor_end' => $w['next_suhoor_end']->format('H:i'),
            'suhoor_meal' => $w['suhoor_end']->subMinutes(30)->format('H:i'),
            'iftar_meal' => $w['iftar']->format('H:i'),
            'dinner_meal' => $w['iftar']->addMinutes(150)->format('H:i'),
            'snack_meal' => $w['iftar']->addMinutes(intdiv($nightMinutes, 2))->format('H:i'),
            'fasting_minutes' => (int) $w['suhoor_end']->diffInMinutes($w['iftar']),
        ];
    }

    /**
     * The `fasting` block of the client day view (contract shape).
     *
     * @return array{mode: string, window_start: string|null, window_end: string|null}|null
     */
    public function windowFor(?MealPlan $plan, string $date): ?array
    {
        if (! $plan) {
            return null;
        }
        $settings = $plan->fasting ?? [];
        if ($plan->type === 'ramadan') {
            $t = $this->ramadanTimes($date, $settings);

            return ['mode' => 'ramadan', 'window_start' => $t['iftar'], 'window_end' => $t['next_suhoor_end']];
        }
        if ($plan->type === 'fasting') {
            return ['mode' => 'intermittent', 'window_start' => $settings['window_start'] ?? null, 'window_end' => $settings['window_end'] ?? null];
        }

        return null;
    }

    /**
     * Shifted slot and time for a meal of a Ramadan plan.
     *
     * @param  array<string, string>  $times  from ramadanTimes()
     * @return array{slot: string, time: string}
     */
    public function shiftMeal(string $slot, array $times): array
    {
        return match ($slot) {
            'breakfast', 'suhoor' => ['slot' => 'suhoor', 'time' => $times['suhoor_meal']],
            'lunch', 'iftar' => ['slot' => 'iftar', 'time' => $times['iftar_meal']],
            'dinner' => ['slot' => 'dinner', 'time' => $times['dinner_meal']],
            default => ['slot' => $slot, 'time' => $times['snack_meal']],
        };
    }
}
