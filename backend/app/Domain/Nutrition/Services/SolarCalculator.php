<?php

namespace App\Domain\Nutrition\Services;

use Carbon\CarbonImmutable;
use DateTimeZone;

/**
 * Pure-PHP solar position calculator (NOAA / praytimes.org formulas) used for
 * fasting windows: Fajr = sun 18° below the horizon before sunrise (suhoor
 * ends), Maghrib = sunset with refraction (−0.833°, iftar).
 *
 * All computations are done in UTC and converted to the city's IANA time
 * zone so DST (Paris, London) is handled by the timezone database.
 */
final class SolarCalculator
{
    public const FAJR_ANGLE = 18.0;

    public const SUNSET_ANGLE = 0.833;

    /** name => [lat, lng, timezone] */
    public const CITIES = [
        'doha' => [25.2854, 51.5310, 'Asia/Qatar'],
        'riyadh' => [24.7136, 46.6753, 'Asia/Riyadh'],
        'jeddah' => [21.4858, 39.1925, 'Asia/Riyadh'],
        'dubai' => [25.2048, 55.2708, 'Asia/Dubai'],
        'abu_dhabi' => [24.4539, 54.3773, 'Asia/Dubai'],
        'kuwait' => [29.3759, 47.9774, 'Asia/Kuwait'],
        'manama' => [26.2285, 50.5860, 'Asia/Bahrain'],
        'muscat' => [23.5880, 58.3829, 'Asia/Muscat'],
        'cairo' => [30.0444, 31.2357, 'Africa/Cairo'],
        'casablanca' => [33.5731, -7.5898, 'Africa/Casablanca'],
        'rabat' => [34.0209, -6.8416, 'Africa/Casablanca'],
        'algiers' => [36.7538, 3.0588, 'Africa/Algiers'],
        'tunis' => [36.8065, 10.1815, 'Africa/Tunis'],
        'paris' => [48.8566, 2.3522, 'Europe/Paris'],
        'london' => [51.5074, -0.1278, 'Europe/London'],
    ];

    /** @return array{lat: float, lng: float, timezone: string}|null */
    public static function city(string $name): ?array
    {
        $key = str_replace([' ', '-'], '_', strtolower(trim($name)));
        $city = self::CITIES[$key] ?? null;

        return $city ? ['lat' => $city[0], 'lng' => $city[1], 'timezone' => $city[2]] : null;
    }

    /**
     * Times for a local calendar date at a location.
     *
     * @return array{fajr: CarbonImmutable, sunrise: CarbonImmutable, dhuhr: CarbonImmutable, maghrib: CarbonImmutable}
     */
    public function times(string $date, float $lat, float $lng, string $timezone): array
    {
        $tz = new DateTimeZone($timezone);
        $day = CarbonImmutable::parse($date, $tz)->startOfDay();
        $jd = $this->julianDay((int) $day->year, (int) $day->month, (int) $day->day) - $lng / 360;

        // Local apparent solar noon, then shifted to UTC by longitude.
        $noonUtc = $this->midDay($jd + 12 / 24) - $lng / 15;

        $sunsetOffset = $this->hourAngle(self::SUNSET_ANGLE, $lat, $jd + 18 / 24);
        $sunriseOffset = $this->hourAngle(self::SUNSET_ANGLE, $lat, $jd + 6 / 24);
        $fajrOffset = $this->hourAngle(self::FAJR_ANGLE, $lat, $jd + 5 / 24);

        $nightHours = 24 - (($sunsetOffset ?? 6.0) + ($sunriseOffset ?? 6.0));
        $portion = $nightHours * self::FAJR_ANGLE / 60;
        if ($fajrOffset === null || $fajrOffset - ($sunriseOffset ?? 6.0) > $portion) {
            // High latitudes in summer: the sun never reaches −18°. Angle-based rule: Fajr = sunrise − night × 18/60.
            $fajrOffset = ($sunriseOffset ?? 6.0) + $portion;
        }

        $base = CarbonImmutable::create((int) $day->year, (int) $day->month, (int) $day->day, 0, 0, 0, 'UTC');
        $at = fn (float $hoursUtc) => $base->addSeconds((int) round($hoursUtc * 3600))->setTimezone($tz);

        return [
            'fajr' => $at($noonUtc - $fajrOffset),
            'sunrise' => $at($noonUtc - ($sunriseOffset ?? 6.0)),
            'dhuhr' => $at($noonUtc),
            'maghrib' => $at($noonUtc + ($sunsetOffset ?? 6.0)),
        ];
    }

    /**
     * Fasting window for a date: suhoor ends at Fajr of that date, iftar at its Maghrib.
     *
     * @return array{suhoor_end: CarbonImmutable, iftar: CarbonImmutable, next_suhoor_end: CarbonImmutable}
     */
    public function fastingWindow(string $date, float $lat, float $lng, string $timezone): array
    {
        $today = $this->times($date, $lat, $lng, $timezone);
        $next = $this->times(CarbonImmutable::parse($date)->addDay()->toDateString(), $lat, $lng, $timezone);

        return ['suhoor_end' => $today['fajr'], 'iftar' => $today['maghrib'], 'next_suhoor_end' => $next['fajr']];
    }

    private function julianDay(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year--;
            $month += 12;
        }
        $a = intdiv($year, 100);
        $b = 2 - $a + intdiv($a, 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }

    /** @return array{0: float, 1: float} [declination°, equation of time (hours)] */
    private function sunPosition(float $jd): array
    {
        $d = $jd - 2451545.0;
        $g = $this->fixAngle(357.529 + 0.98560028 * $d);
        $q = $this->fixAngle(280.459 + 0.98564736 * $d);
        $l = $this->fixAngle($q + 1.915 * $this->sin($g) + 0.020 * $this->sin(2 * $g));
        $e = 23.439 - 0.00000036 * $d;
        $ra = $this->atan2($this->cos($e) * $this->sin($l), $this->cos($l)) / 15;
        $eqt = $q / 15 - $this->fixHour($ra);
        $decl = $this->asin($this->sin($e) * $this->sin($l));

        return [$decl, $eqt];
    }

    /** Apparent solar noon (local mean hours) for a Julian date. */
    private function midDay(float $jd): float
    {
        [, $eqt] = $this->sunPosition($jd);

        return $this->fixHour(12 - $eqt);
    }

    /** Hours between solar noon and the moment the sun is $angle° below the horizon; null if never reached. */
    private function hourAngle(float $angle, float $lat, float $jd): ?float
    {
        [$decl] = $this->sunPosition($jd);
        $cos = (-$this->sin($angle) - $this->sin($decl) * $this->sin($lat)) / ($this->cos($decl) * $this->cos($lat));
        if ($cos < -1 || $cos > 1) {
            return null;
        }

        return rad2deg(acos($cos)) / 15;
    }

    private function sin(float $d): float
    {
        return sin(deg2rad($d));
    }

    private function cos(float $d): float
    {
        return cos(deg2rad($d));
    }

    private function asin(float $x): float
    {
        return rad2deg(asin($x));
    }

    private function atan2(float $y, float $x): float
    {
        return rad2deg(atan2($y, $x));
    }

    private function fixAngle(float $a): float
    {
        $a = fmod($a, 360.0);

        return $a < 0 ? $a + 360 : $a;
    }

    private function fixHour(float $h): float
    {
        $h = fmod($h, 24.0);

        return $h < 0 ? $h + 24 : $h;
    }
}
