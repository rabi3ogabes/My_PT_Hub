<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Workouts\Events\PersonalRecordAchieved;
use App\Domain\Workouts\Events\WorkoutCompleted;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutSession;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Offline-first workout logger. Sessions and sets carry client ULIDs: every
 * write is an idempotent upsert on that id, sets are append-only by id (never
 * hard-deleted), and completing a session is idempotent.
 */
class SessionLogger
{
    public const SET_FIELDS = ['exercise_id', 'workout_exercise_id', 'set_number', 'set_type', 'reps', 'weight_kg', 'duration_seconds', 'distance_m', 'rpe', 'is_completed', 'completed_at'];

    public function __construct(private readonly PersonalRecordDetector $records) {}

    /**
     * Starts (or returns) a session. Idempotent on the client-generated id.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: WorkoutSession, 1: bool} [session, created]
     */
    public function start(User $client, array $data): array
    {
        $id = isset($data['id']) ? strtolower((string) $data['id']) : null;
        if ($id !== null) {
            $existing = $this->findOwnedOrConflict($client, $id);
            if ($existing) {
                return [$existing, false];
            }
        }

        $workout = null;
        if (! empty($data['workout_id'])) {
            $workout = Workout::query()->find($data['workout_id']) ?? throw ApiException::invalid('validation_failed', __('workouts.workout_not_found'), ['workout_id' => [__('workouts.workout_not_found')]]);
        }
        $assignment = null;
        if (! empty($data['program_assignment_id'])) {
            $assignment = ProgramAssignment::query()->where('client_id', $client->id)->find($data['program_assignment_id'])
                ?? throw ApiException::invalid('validation_failed', __('workouts.assignment_not_found'), ['program_assignment_id' => [__('workouts.assignment_not_found')]]);
        }
        $day = null;
        if (! empty($data['program_day_id'])) {
            $day = ProgramDay::query()->with('week')->find($data['program_day_id']);
            $ownsDay = $day && ProgramAssignment::query()->where('client_id', $client->id)
                ->where('program_id', $day->week?->program_id)
                ->when($assignment, fn ($q) => $q->whereKey($assignment->id))
                ->exists();
            if (! $ownsDay) {
                throw ApiException::invalid('validation_failed', __('workouts.program_day_not_found'), ['program_day_id' => [__('workouts.program_day_not_found')]]);
            }
            if (! $assignment) {
                $assignment = ProgramAssignment::query()->where('client_id', $client->id)->where('program_id', $day->week->program_id)
                    ->orderByRaw("case when status in ('active','paused') then 0 else 1 end")->latest()->first();
            }
            if (! $workout && $day->workout_id) {
                $workout = Workout::query()->find($day->workout_id);
            }
        }

        $session = new WorkoutSession([
            'client_id' => $client->id,
            'workout_id' => $workout?->id,
            'program_assignment_id' => $assignment?->id,
            'program_day_id' => $day?->id,
            'name' => $data['name'] ?? $workout?->name ?? __('workouts.freestyle_session'),
            'status' => 'in_progress',
            'started_at' => isset($data['started_at']) ? Carbon::parse($data['started_at'])->utc() : now(),
            'total_volume_kg' => 0,
        ]);
        if ($id !== null) {
            $session->id = $id;
        }
        try {
            $session->save();
        } catch (UniqueConstraintViolationException) {
            // A concurrent retry created it first.
            return [$this->findOwnedOrConflict($client, (string) $id) ?? throw ApiException::conflict('session_conflict', __('workouts.session_conflict')), false];
        }

        return [$session, true];
    }

    /**
     * Batch upsert of sets by client id. Re-sending a set is a no-op unless values changed
     * (last write wins); sets are never removed here.
     *
     * @param  list<array<string, mixed>>  $sets
     * @return list<SessionSet>
     */
    public function upsertSets(WorkoutSession $session, array $sets): array
    {
        if ($session->status === 'abandoned') {
            throw ApiException::conflict('session_abandoned', __('workouts.session_abandoned'));
        }
        $ids = collect($sets)->pluck('id')->map(fn ($id) => strtolower((string) $id))->all();

        $exerciseIds = collect($sets)->pluck('exercise_id')->filter()->unique()->values();
        $visible = Exercise::query()->withTrashed()->whereIn('id', $exerciseIds)->pluck('id')->flip();
        foreach ($sets as $i => $set) {
            if (! isset($visible[$set['exercise_id'] ?? ''])) {
                throw ApiException::invalid('validation_failed', __('api.validation_failed'), ["sets.$i.exercise_id" => [__('workouts.exercise_not_found')]]);
            }
        }

        $result = DB::transaction(function () use ($session, $sets, $ids) {
            $existing = SessionSet::withTrashed()->whereIn('id', $ids)->get()->keyBy('id');
            $foreign = $existing->first(fn (SessionSet $s) => $s->workout_session_id !== $session->id);
            if ($foreign) {
                throw ApiException::conflict('set_conflict', __('workouts.set_conflict'));
            }
            $nextNumber = [];
            $saved = [];
            foreach ($sets as $data) {
                $id = strtolower((string) $data['id']);
                $set = $existing->get($id) ?? new SessionSet(['workout_session_id' => $session->id]);
                $set->id = $id;
                foreach (self::SET_FIELDS as $field) {
                    if (array_key_exists($field, $data)) {
                        $set->{$field} = $field === 'completed_at' && $data[$field] !== null ? Carbon::parse($data[$field])->utc() : $data[$field];
                    }
                }
                if (! $set->exists) {
                    $set->set_type ??= 'working';
                    $set->is_completed ??= true;
                    if ($set->set_number === null) {
                        $key = $set->exercise_id;
                        $nextNumber[$key] ??= (int) SessionSet::query()->where('workout_session_id', $session->id)->where('exercise_id', $key)->max('set_number');
                        $set->set_number = ++$nextNumber[$key];
                    }
                    if ($set->is_completed && $set->completed_at === null) {
                        $set->completed_at = now();
                    }
                }
                if ($set->trashed() && ($data['restore'] ?? true)) {
                    $set->deleted_at = null; // re-sent after a delete → the client wants it back
                }
                if (! $set->exists || $set->isDirty()) {
                    $set->save();
                }
                $saved[] = $set;
            }
            $session->touch();

            return $saved;
        });

        if ($session->isCompleted()) {
            $this->recalculate($session);
        }

        return $result;
    }

    /** Soft-deletes one set (offline "remove set"); the row is kept as a tombstone for sync. */
    public function deleteSet(WorkoutSession $session, string $setId): void
    {
        $set = SessionSet::query()->where('workout_session_id', $session->id)->whereKey(strtolower($setId))->first();
        if ($set) {
            $set->delete();
            $session->touch();
            if ($session->isCompleted()) {
                $this->recalculate($session);
            }
        }
    }

    /**
     * Completes a session (idempotent): duration, volume and PR detection.
     * WorkoutCompleted / PersonalRecordAchieved fire only on the first completion.
     *
     * @param  array<string, mixed>  $data
     */
    public function complete(WorkoutSession $session, array $data = []): WorkoutSession
    {
        if ($session->status === 'abandoned') {
            throw ApiException::conflict('session_abandoned', __('workouts.session_abandoned'));
        }

        return DB::transaction(function () use ($session, $data) {
            $session = WorkoutSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $first = $session->status !== 'completed';

            foreach (['perceived_effort', 'mood', 'notes'] as $field) {
                if (array_key_exists($field, $data)) {
                    $session->{$field} = $data[$field];
                }
            }
            if ($first) {
                $ended = isset($data['ended_at']) ? Carbon::parse($data['ended_at'])->utc() : now();
                if ($ended->lessThan($session->started_at)) {
                    $ended = $session->started_at->copy();
                }
                $session->ended_at = $ended;
                $session->status = 'completed';
            }
            $session->duration_seconds = (int) max(0, $session->started_at->diffInSeconds($session->ended_at, true));
            $session->total_volume_kg = $this->totalVolume($session);
            $session->save();

            if ($first) {
                $records = $this->records->detect($session);
                event(new WorkoutCompleted($session, $records));
                foreach ($records as $record) {
                    event(new PersonalRecordAchieved($record));
                }
            }

            return $session;
        });
    }

    /** Abandons an in-progress session (sets are kept). */
    public function abandon(WorkoutSession $session): WorkoutSession
    {
        if ($session->status === 'in_progress') {
            $session->forceFill(['status' => 'abandoned', 'ended_at' => now()])->save();
        }

        return $session;
    }

    /** Re-derives totals and records after late (offline) set changes on a completed session. */
    public function recalculate(WorkoutSession $session): void
    {
        DB::transaction(function () use ($session) {
            $session->total_volume_kg = $this->totalVolume($session);
            $session->save();
            $before = PersonalRecord::query()->where('workout_session_id', $session->id)
                ->whereNotNull('previous_value')->get()->map(fn ($r) => $r->exercise_id.'|'.$r->type)->all();
            foreach ($this->records->detect($session) as $record) {
                if (! in_array($record->exercise_id.'|'.$record->type, $before, true)) {
                    event(new PersonalRecordAchieved($record));
                }
            }
        });
    }

    public function totalVolume(WorkoutSession $session): float
    {
        $sets = $this->records->qualifying(SessionSet::query()->where('workout_session_id', $session->id)->get());

        return round($this->records->volume($sets), 2);
    }

    /** The client's own session, null when unknown, conflict when the id belongs to someone else. */
    private function findOwnedOrConflict(User $client, string $id): ?WorkoutSession
    {
        $row = WorkoutSession::withoutGlobalScopes()->withTrashed()->whereKey($id)->first();
        if (! $row) {
            return null;
        }
        $tenantId = app(TenantContext::class)->id();
        if ($row->client_id !== $client->id || $row->tenant_id !== $tenantId) {
            throw ApiException::conflict('session_conflict', __('workouts.session_conflict'));
        }
        if ($row->trashed()) {
            $row->restore();
        }

        return $row;
    }

    /** Local calendar date of a session for the client. */
    public static function localDate(WorkoutSession $session, ?User $client): string
    {
        $tz = $client?->timezone ?: 'UTC';

        return CarbonImmutable::parse($session->started_at)->setTimezone($tz)->toDateString();
    }
}
