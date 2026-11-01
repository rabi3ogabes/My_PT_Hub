<?php

namespace App\Domain\Marketing\Services;

use App\Domain\Tenancy\Models\CoachProfile;

/**
 * Ranks published coaches against a standalone client's quiz answers.
 * Transparent additive scoring (0–100) with hard filters for in-person city
 * and coach gender preference; every point awarded comes with a reason key.
 */
final class CoachMatchService
{
    /** Goal → specialty keywords coaches commonly list. */
    public const GOAL_SPECIALTIES = [
        'fat_loss' => ['fat_loss', 'weight_loss', 'nutrition', 'body_recomposition'],
        'muscle_gain' => ['hypertrophy', 'muscle_gain', 'bodybuilding', 'strength'],
        'strength' => ['strength', 'powerlifting', 'weightlifting'],
        'performance' => ['sports_performance', 'running', 'hyrox', 'crossfit', 'endurance'],
        'health' => ['wellness', 'mobility', 'rehab', 'pre_postnatal', 'seniors', 'health'],
    ];

    public function __construct(private readonly CoachDirectoryService $directory) {}

    /**
     * @param  array{goal?: string|null, specialties?: list<string>, languages?: list<string>, mode?: string|null, city?: string|null, country?: string|null, budget_max_minor?: int|null, currency?: string|null, coach_gender?: string|null}  $answers
     * @return list<array{profile: CoachProfile, score: int, reasons: list<string>}>
     */
    public function rank(array $answers, int $limit = 10): array
    {
        $mode = $answers['mode'] ?? 'any';
        $city = mb_strtolower(trim((string) ($answers['city'] ?? '')));
        $query = $this->directory->published();
        if (filled($answers['country'] ?? null) && $mode === 'in_person') {
            $query->where('coach_profiles.country', strtoupper((string) $answers['country']));
        }
        if (filled($answers['coach_gender'] ?? null) && $answers['coach_gender'] !== 'any') {
            $query->where('coach_profiles.gender', $answers['coach_gender']);
        }
        if ($mode === 'in_person') {
            $query->where('coach_profiles.offers_in_person', true);
            if ($city !== '') {
                $query->whereRaw('LOWER(coach_profiles.city) = ?', [$city]);
            }
        } elseif ($mode === 'online') {
            $query->where('coach_profiles.offers_online', true);
        }

        $wanted = array_map('strtolower', array_merge(
            self::GOAL_SPECIALTIES[$answers['goal'] ?? ''] ?? [],
            (array) ($answers['specialties'] ?? []),
        ));
        $languages = array_map('strtolower', (array) ($answers['languages'] ?? []));

        $ranked = $query->limit(500)->get()->map(function (CoachProfile $profile) use ($wanted, $languages, $mode, $city, $answers) {
            $score = 0;
            $reasons = [];
            $specialties = array_map('strtolower', (array) ($profile->specialties ?? []));

            if ($wanted !== [] && array_intersect($wanted, $specialties) !== []) {
                $score += 30;
                $reasons[] = 'specialty';
            }
            $coachLanguages = array_map('strtolower', (array) ($profile->languages ?? []));
            if ($languages === []) {
                $score += 10;
            } elseif (array_intersect($languages, $coachLanguages) !== []) {
                $score += 20;
                $reasons[] = 'language';
            }
            if ($mode === 'online' && $profile->offers_online) {
                $score += 15;
                $reasons[] = 'online';
            } elseif ($mode === 'in_person') {
                $score += 15;
                $reasons[] = 'in_person';
            } else {
                $score += 10;
            }
            if ($city !== '' && mb_strtolower((string) $profile->city) === $city) {
                $score += 10;
                $reasons[] = 'city';
            }
            $budget = $answers['budget_max_minor'] ?? null;
            $sameCurrency = empty($answers['currency']) || strtoupper((string) $answers['currency']) === strtoupper((string) $profile->tenant?->currency);
            if ($budget !== null && $profile->price_from_minor !== null && $sameCurrency) {
                if ((int) $profile->price_from_minor <= (int) $budget) {
                    $score += 10;
                    $reasons[] = 'budget';
                }
            } else {
                $score += 5;
            }
            if ($profile->rating_count > 0) {
                $score += (int) round(($profile->rating_avg / 5) * 10);
                if ($profile->rating_avg >= 4.5) {
                    $reasons[] = 'rating';
                }
            }
            if ($profile->is_verified) {
                $score += 5;
                $reasons[] = 'verified';
            }

            return ['profile' => $profile, 'score' => min(100, $score), 'reasons' => $reasons];
        })->sort(function (array $a, array $b) {
            return [$b['score'], (int) $b['profile']->is_featured, $b['profile']->rating_avg, $a['profile']->id]
                <=> [$a['score'], (int) $a['profile']->is_featured, $a['profile']->rating_avg, $b['profile']->id];
        })->values()->take($limit)->all();

        return $ranked;
    }
}
