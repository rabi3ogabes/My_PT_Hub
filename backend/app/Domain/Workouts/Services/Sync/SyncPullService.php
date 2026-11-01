<?php

namespace App\Domain\Workouts\Services\Sync;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\WaterLog;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Domain\Workouts\Services\TodayService;
use App\Http\Api\V1\Resources\Workouts\SessionResource;
use App\Http\Api\V1\Resources\Workouts\SessionSetResource;
use Carbon\CarbonImmutable;

/**
 * GET /sync/pull — everything the client app stores offline that changed since a
 * server timestamp, plus tombstones (soft-deleted ids). Without `since` the
 * initial window is the last N days (config workouts.sync_initial_days).
 */
class SyncPullService
{
    public const LIMIT = 2000;

    /** @return array<string, mixed> */
    public function pull(User $client, ?CarbonImmutable $since): array
    {
        $serverTime = CarbonImmutable::now('UTC');
        $floor = $since ?? $serverTime->subDays((int) config('workouts.sync_initial_days', 90));
        $sinceSql = $floor->utc()->toDateTimeString();

        $sessionIds = WorkoutSession::withTrashed()->where('client_id', $client->id)->pluck('id');
        $sessions = WorkoutSession::query()->where('client_id', $client->id)->where('updated_at', '>=', $sinceSql)
            ->orderBy('updated_at')->limit(self::LIMIT)->get();
        $sets = SessionSet::query()->whereIn('workout_session_id', $sessionIds)->where('updated_at', '>=', $sinceSql)
            ->orderBy('updated_at')->limit(self::LIMIT * 5)->get();

        $foodLogs = FoodLog::query()->where('client_id', $client->id)->where('updated_at', '>=', $sinceSql)
            ->orderBy('updated_at')->limit(self::LIMIT)->get();
        $waterLogs = WaterLog::query()->where('client_id', $client->id)->where('updated_at', '>=', $sinceSql)
            ->orderBy('updated_at')->limit(self::LIMIT)->get();
        $assignmentIds = HabitAssignment::query()->where('client_id', $client->id)->pluck('id');
        $habitLogs = HabitLog::query()->whereIn('habit_assignment_id', $assignmentIds)->where('updated_at', '>=', $sinceSql)
            ->orderBy('updated_at')->limit(self::LIMIT)->get();
        $metrics = BodyMetric::query()->where('client_id', $client->id)->where('updated_at', '>=', $sinceSql)
            ->orderBy('updated_at')->limit(self::LIMIT)->get();
        $bookings = Booking::query()->where('client_id', $client->id)->where('updated_at', '>=', $sinceSql)
            ->with(['event.coach', 'event.location'])->orderBy('updated_at')->limit(self::LIMIT)->get();

        $deleted = [
            'sessions' => $since ? WorkoutSession::onlyTrashed()->where('client_id', $client->id)->where('deleted_at', '>=', $sinceSql)->pluck('id')->all() : [],
            'session_sets' => $since ? SessionSet::onlyTrashed()->whereIn('workout_session_id', $sessionIds)->where('deleted_at', '>=', $sinceSql)->pluck('id')->all() : [],
            'food_logs' => $since ? FoodLog::onlyTrashed()->where('client_id', $client->id)->where('deleted_at', '>=', $sinceSql)->pluck('id')->all() : [],
        ];

        return [
            'server_time' => $serverTime->toIso8601String(),
            'changes' => [
                'sessions' => $sessions->map(fn (WorkoutSession $s) => [...SessionResource::flat($s), 'updated_at' => $s->updated_at?->toIso8601String()])->all(),
                'session_sets' => $sets->map(fn (SessionSet $s) => [...SessionSetResource::shape($s), 'session_id' => $s->workout_session_id, 'updated_at' => $s->updated_at?->toIso8601String()])->all(),
                'food_logs' => $foodLogs->map(fn (FoodLog $l) => self::foodLog($l))->all(),
                'water_logs' => $waterLogs->map(fn (WaterLog $l) => self::waterLog($l))->all(),
                'habit_logs' => $habitLogs->map(fn (HabitLog $l) => self::habitLog($l))->all(),
                'body_metrics' => $metrics->map(fn (BodyMetric $m) => self::bodyMetric($m))->all(),
                'bookings' => $bookings->map(fn (Booking $b) => [...TodayService::booking($b), 'updated_at' => $b->updated_at?->toIso8601String()])->all(),
            ],
            'deleted' => $deleted,
        ];
    }

    /** @return array<string, mixed> */
    public static function foodLog(FoodLog $l): array
    {
        return [
            'id' => $l->id,
            'log_date' => $l->log_date?->toDateString(),
            'slot' => $l->slot,
            'food_id' => $l->food_id,
            'recipe_id' => $l->recipe_id,
            'name' => $l->name,
            'quantity' => $l->quantity,
            'unit' => $l->unit,
            'kcal' => $l->kcal,
            'protein_g' => $l->protein_g,
            'carbs_g' => $l->carbs_g,
            'fat_g' => $l->fat_g,
            'source' => $l->source,
            'logged_at' => $l->logged_at?->toIso8601String(),
            'updated_at' => $l->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public static function waterLog(WaterLog $l): array
    {
        return [
            'id' => $l->id,
            'log_date' => $l->log_date?->toDateString(),
            'ml' => (int) $l->ml,
            'logged_at' => $l->logged_at?->toIso8601String(),
            'updated_at' => $l->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public static function habitLog(HabitLog $l): array
    {
        return [
            'id' => $l->id,
            'assignment_id' => $l->habit_assignment_id,
            'date' => $l->log_date?->toDateString(),
            'value' => $l->value,
            'completed' => (bool) $l->completed,
            'updated_at' => $l->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public static function bodyMetric(BodyMetric $m): array
    {
        return [
            'id' => $m->id,
            'measured_on' => $m->measured_on?->toDateString(),
            'weight_kg' => $m->weight_kg,
            'body_fat_pct' => $m->body_fat_pct,
            'measurements' => $m->measurements,
            'source' => $m->source,
            'updated_at' => $m->updated_at?->toIso8601String(),
        ];
    }
}
