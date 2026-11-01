<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Progress\Services\TrainingSchedule;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientProfile;
use Carbon\CarbonImmutable;

/** Stores and resolves a client's nutrition targets (effective-dated, per day type). */
final class TargetService
{
    public function __construct(
        private readonly NutritionCalculator $calculator,
        private readonly TrainingSchedule $schedule,
    ) {}

    /** Target that applies on a date: training/rest specific when defined, otherwise "all". */
    public function forDate(string $clientId, string $date): ?NutritionTarget
    {
        $candidates = NutritionTarget::query()
            ->where('client_id', $clientId)
            ->whereDate('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        if ($candidates->isEmpty()) {
            return null;
        }
        $hasTypes = $candidates->contains(fn ($t) => $t->day_type !== 'all');
        if ($hasTypes) {
            $type = $this->schedule->isTrainingDay($clientId, $date) ? 'training' : 'rest';
            $typed = $candidates->firstWhere('day_type', $type);
            $all = $candidates->firstWhere('day_type', 'all');
            if ($typed && (! $all || $typed->effective_from->gte($all->effective_from))) {
                return $typed;
            }

            return $all ?? $typed;
        }

        return $candidates->first();
    }

    /**
     * Calculator inputs pre-filled from the client profile and latest weigh-in.
     *
     * @return array<string, mixed>
     */
    public function profileInputs(User $client): array
    {
        $profile = ClientProfile::query()->where('user_id', $client->id)->first();
        $metric = BodyMetric::query()->where('client_id', $client->id)->whereNotNull('weight_kg')->orderByDesc('measured_on')->first();
        $bodyFat = BodyMetric::query()->where('client_id', $client->id)->whereNotNull('body_fat_pct')->orderByDesc('measured_on')->value('body_fat_pct');

        return array_filter([
            'sex' => $this->sex($profile?->gender),
            'age' => $profile?->age(),
            'height_cm' => $profile?->height_cm,
            'weight_kg' => $metric?->weight_kg,
            'body_fat_pct' => $bodyFat !== null ? (float) $bodyFat : null,
            'activity' => isset(NutritionCalculator::ACTIVITY_FACTORS[$profile?->activity_level ?? '']) ? $profile->activity_level : null,
            'goal' => isset(NutritionCalculator::GOAL_ADJUSTMENTS[$profile?->goal ?? '']) ? $profile->goal : null,
        ], fn ($v) => $v !== null);
    }

    public function clientSex(User $client): ?string
    {
        return $this->sex(ClientProfile::query()->where('user_id', $client->id)->value('gender'));
    }

    /**
     * Stores explicit targets after the safety check.
     *
     * @param  array<string, mixed>  $data  kcal, protein_g, carbs_g, fat_g, fiber_g?, water_ml?, day_type, effective_from, override_safety
     * @param  array<string, mixed>|null  $calculation
     */
    public function store(User $client, array $data, ?array $calculation = null): NutritionTarget
    {
        $override = (bool) ($data['override_safety'] ?? false);
        $floor = $this->calculator->safetyFloor($this->clientSex($client));
        if (! $override && (int) $data['kcal'] < $floor) {
            throw ApiException::invalid('below_safety_floor', __('nutrition.errors.below_safety_floor', ['kcal' => $floor]), ['kcal' => [__('nutrition.errors.below_safety_floor', ['kcal' => $floor])]]);
        }

        return NutritionTarget::create([
            'client_id' => $client->id,
            'day_type' => $data['day_type'] ?? 'all',
            'kcal' => (int) round($data['kcal']),
            'protein_g' => (int) round($data['protein_g']),
            'carbs_g' => (int) round($data['carbs_g']),
            'fat_g' => (int) round($data['fat_g']),
            'fiber_g' => isset($data['fiber_g']) ? (int) round($data['fiber_g']) : $this->calculator->fiber((float) $data['kcal']),
            'water_ml' => (int) ($data['water_ml'] ?? 2500),
            'effective_from' => $data['effective_from'] ?? CarbonImmutable::now($client->timezone ?: 'UTC')->toDateString(),
            'calculation' => $calculation,
            'override_safety' => $override,
        ]);
    }

    private function sex(?string $gender): ?string
    {
        return match (strtolower((string) $gender)) {
            'male', 'm', 'man' => 'male',
            'female', 'f', 'woman' => 'female',
            default => null,
        };
    }
}
