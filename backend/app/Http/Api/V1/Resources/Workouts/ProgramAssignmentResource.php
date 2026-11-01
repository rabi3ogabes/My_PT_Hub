<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Services\ProgramSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProgramAssignment */
class ProgramAssignmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var ProgramAssignment $a */
        $a = $this->resource;
        $a->loadMissing(['program' => fn ($q) => $q->withTrashed(), 'client']);
        $schedule = app(ProgramSchedule::class);
        $today = ProgramSchedule::today($a->client);

        return [
            'id' => $a->id,
            'program' => $a->program ? ['id' => $a->program->id, 'name' => $a->program->translate('name')] : null,
            'client' => $a->client ? ['id' => $a->client->id, 'name' => $a->client->name] : null,
            'start_date' => $a->start_date->toDateString(),
            'status' => $a->status,
            'unlock_mode' => $a->unlock_mode,
            'current_week' => $schedule->currentWeek($a, $today),
            'progress_pct' => $schedule->progressPct($a),
            'shift_days' => (int) $a->shift_days,
            'paused_at' => $a->paused_at?->toDateString(),
        ];
    }
}
