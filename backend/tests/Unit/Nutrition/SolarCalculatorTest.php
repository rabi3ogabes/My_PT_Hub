<?php

use App\Domain\Nutrition\Services\SolarCalculator;

beforeEach(fn () => $this->solar = new SolarCalculator);

it('computes Doha iftar and suhoor times during Ramadan', function () {
    $city = SolarCalculator::city('Doha');
    $t = $this->solar->times('2026-03-01', $city['lat'], $city['lng'], $city['timezone']);
    $iftar = $t['maghrib']->format('H:i');
    $fajr = $t['fajr']->format('H:i');

    expect($iftar >= '17:00' && $iftar <= '18:30')->toBeTrue("iftar $iftar")
        ->and($fajr >= '04:15' && $fajr <= '05:00')->toBeTrue("fajr $fajr")
        ->and($t['maghrib']->getTimezone()->getName())->toBe('Asia/Qatar');
});

it('handles every supported city and DST cities', function () {
    foreach (array_keys(SolarCalculator::CITIES) as $name) {
        $c = SolarCalculator::city($name);
        $t = $this->solar->times('2026-03-10', $c['lat'], $c['lng'], $c['timezone']);
        expect($t['fajr']->lt($t['sunrise']))->toBeTrue($name)
            ->and($t['sunrise']->lt($t['dhuhr']))->toBeTrue($name)
            ->and($t['dhuhr']->lt($t['maghrib']))->toBeTrue($name);
    }
    // Paris after the DST switch: sunset after 20:00 local in late April.
    $paris = SolarCalculator::city('paris');
    expect($this->solar->times('2026-04-25', $paris['lat'], $paris['lng'], $paris['timezone'])['maghrib']->format('H') >= '20')->toBeTrue();
});

it('falls back to the angle-based rule when the sun never reaches -18° (London in June)', function () {
    $c = SolarCalculator::city('london');
    $t = $this->solar->times('2026-06-21', $c['lat'], $c['lng'], $c['timezone']);
    expect($t['fajr']->lt($t['sunrise']))->toBeTrue()
        ->and($t['fajr']->format('H:i') > '01:00')->toBeTrue();
});

it('accepts arbitrary coordinates and matches the named city', function () {
    $doha = SolarCalculator::city('doha');
    $byName = $this->solar->times('2026-03-01', $doha['lat'], $doha['lng'], $doha['timezone']);
    $byCoords = $this->solar->times('2026-03-01', 25.2854, 51.5310, 'Asia/Qatar');
    expect($byCoords['maghrib']->format('H:i'))->toBe($byName['maghrib']->format('H:i'));
});
