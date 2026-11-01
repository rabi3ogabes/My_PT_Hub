<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Session with sets and the PRs it set (records that beat a previous best).
 *
 * @mixin WorkoutSession
 */
class SessionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var WorkoutSession $s */
        $s = $this->resource;
        $s->loadMissing(['sets', 'personalRecords.exercise' => fn ($q) => $q->withTrashed()]);

        return [
            ...self::flat($s),
            'sets' => $s->sets->map(fn (SessionSet $set) => SessionSetResource::shape($set))->values()->all(),
            'personal_records' => $s->personalRecords
                ->filter(fn (PersonalRecord $r) => $r->previous_value !== null)
                ->map(fn (PersonalRecord $r) => (new PersonalRecordResource($r))->toArray($request))->values()->all(),
        ];
    }

    /** Session fields without nested collections (also used by /sync/pull). @return array<string, mixed> */
    public static function flat(WorkoutSession $s): array
    {
        return [
            'id' => $s->id,
            'workout_id' => $s->workout_id,
            'program_day_id' => $s->program_day_id,
            'program_assignment_id' => $s->program_assignment_id,
            'name' => $s->name,
            'status' => $s->status,
            'started_at' => $s->started_at?->toIso8601String(),
            'ended_at' => $s->ended_at?->toIso8601String(),
            'duration_seconds' => $s->duration_seconds,
            'total_volume_kg' => (float) $s->total_volume_kg,
            'perceived_effort' => $s->perceived_effort,
            'mood' => $s->mood,
            'notes' => $s->notes,
        ];
    }
}
