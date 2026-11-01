<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutSession;
use Illuminate\Support\Collection;

/**
 * Detects personal records for a session against the client's other sessions
 * that started before it:
 * - e1rm:   best Epley estimate (reps 1–15)
 * - weight: heaviest weight lifted for at least one rep
 * - reps:   more reps at a weight than any previous set at that weight or heavier
 * - volume: total kg × reps on the exercise within the session
 * Re-running is idempotent: the session's records are recomputed from scratch.
 * The first time an exercise is logged a baseline record (previous_value null) is stored
 * so the records board is complete; only records that beat a previous best are "new PRs".
 */
class PersonalRecordDetector
{
    /**
     * @return list<PersonalRecord> records that beat a previous best
     */
    public function detect(WorkoutSession $session): array
    {
        $sets = SessionSet::query()->where('workout_session_id', $session->id)->get();
        $byExercise = $this->qualifying($sets)->groupBy('exercise_id');

        PersonalRecord::query()->where('workout_session_id', $session->id)->delete();
        if ($byExercise->isEmpty()) {
            return [];
        }

        $previousSessionIds = WorkoutSession::query()
            ->where('client_id', $session->client_id)
            ->where('id', '!=', $session->id)
            ->where('status', '!=', 'abandoned')
            ->where(fn ($q) => $q->where('started_at', '<', $session->started_at)
                ->orWhere(fn ($q2) => $q2->where('started_at', $session->started_at)->where('id', '<', $session->id)))
            ->pluck('id');

        $history = $this->qualifying(SessionSet::query()
            ->whereIn('workout_session_id', $previousSessionIds)
            ->whereIn('exercise_id', $byExercise->keys())
            ->get())->groupBy('exercise_id');

        $achievedAt = $session->ended_at ?? now();
        $newRecords = [];

        foreach ($byExercise as $exerciseId => $current) {
            $previous = $history->get($exerciseId, collect());
            $candidates = $this->candidates($current, $previous);
            foreach ($candidates as $c) {
                $record = PersonalRecord::create([
                    'tenant_id' => $session->tenant_id,
                    'client_id' => $session->client_id,
                    'exercise_id' => $exerciseId,
                    'workout_session_id' => $session->id,
                    'session_set_id' => $c['set_id'],
                    'type' => $c['type'],
                    'value' => $c['value'],
                    'previous_value' => $c['previous'],
                    'weight_kg' => $c['weight_kg'],
                    'achieved_at' => $achievedAt,
                ]);
                if ($c['previous'] !== null) {
                    $newRecords[] = $record;
                }
            }
        }

        return $newRecords;
    }

    /**
     * @param  Collection<int, SessionSet>  $current
     * @param  Collection<int, SessionSet>  $previous
     * @return list<array{type: string, value: float, previous: ?float, set_id: ?string, weight_kg: ?float}>
     */
    public function candidates(Collection $current, Collection $previous): array
    {
        $out = [];
        $firstTime = $previous->isEmpty();

        // e1RM (Epley)
        $best = $this->bestBy($current, fn (SessionSet $s) => OneRepMax::epley($s->weight_kg, $s->reps));
        $prevBest = $this->maxOf($previous, fn (SessionSet $s) => OneRepMax::epley($s->weight_kg, $s->reps));
        if ($best && ($firstTime ? true : ($prevBest === null || $best['value'] > $prevBest + 0.001))) {
            $out[] = ['type' => 'e1rm', 'value' => $best['value'], 'previous' => $firstTime ? null : $prevBest, 'set_id' => $best['set']->id, 'weight_kg' => $best['set']->weight_kg];
        }

        // Heaviest weight (≥ 1 rep)
        $best = $this->bestBy($current, fn (SessionSet $s) => ($s->weight_kg ?? 0) > 0 && ($s->reps ?? 0) >= 1 ? $s->weight_kg : null);
        $prevBest = $this->maxOf($previous, fn (SessionSet $s) => ($s->weight_kg ?? 0) > 0 && ($s->reps ?? 0) >= 1 ? $s->weight_kg : null);
        if ($best && ($firstTime || $prevBest === null || $best['value'] > $prevBest + 0.001)) {
            $out[] = ['type' => 'weight', 'value' => $best['value'], 'previous' => $firstTime ? null : $prevBest, 'set_id' => $best['set']->id, 'weight_kg' => $best['set']->weight_kg];
        }

        // Reps at weight: the heaviest set whose reps beat every previous set at ≥ that weight
        // (requires history at that weight — a brand-new heaviest weight is a weight PR instead).
        $repPr = null;
        $ordered = $current->filter(fn (SessionSet $s) => ($s->reps ?? 0) > 0)
            ->sort(fn (SessionSet $a, SessionSet $b) => [(float) ($b->weight_kg ?? 0), (int) $b->reps] <=> [(float) ($a->weight_kg ?? 0), (int) $a->reps]);
        foreach ($ordered as $set) {
            $w = (float) ($set->weight_kg ?? 0);
            if ($firstTime) {
                $repPr = ['type' => 'reps', 'value' => (float) $set->reps, 'previous' => null, 'set_id' => $set->id, 'weight_kg' => $w];
                break;
            }
            $prevReps = $this->maxOf($previous->filter(fn (SessionSet $p) => (float) ($p->weight_kg ?? 0) >= $w - 0.001), fn (SessionSet $p) => $p->reps ? (float) $p->reps : null);
            if ($prevReps !== null && $set->reps > $prevReps) {
                $repPr = ['type' => 'reps', 'value' => (float) $set->reps, 'previous' => $prevReps, 'set_id' => $set->id, 'weight_kg' => $w];
                break;
            }
        }
        if ($repPr) {
            $repPr['weight_kg'] = $repPr['weight_kg'] > 0 ? $repPr['weight_kg'] : null;
            $out[] = $repPr;
        }

        // Session volume on the exercise
        $volume = $this->volume($current);
        if ($volume > 0) {
            $prevVolume = $previous->groupBy('workout_session_id')->map(fn ($sets) => $this->volume($sets))->max();
            if ($firstTime || $prevVolume === null || $volume > $prevVolume + 0.001) {
                $top = $current->sortByDesc(fn (SessionSet $s) => (float) ($s->weight_kg ?? 0) * (int) ($s->reps ?? 0))->first();
                $out[] = ['type' => 'volume', 'value' => round($volume, 2), 'previous' => $firstTime ? null : ($prevVolume !== null ? round((float) $prevVolume, 2) : null), 'set_id' => $top?->id, 'weight_kg' => null];
            }
        }

        return $out;
    }

    /** @param Collection<int, SessionSet> $sets */
    public function volume(Collection $sets): float
    {
        return (float) $sets->sum(fn (SessionSet $s) => (float) ($s->weight_kg ?? 0) * (int) ($s->reps ?? 0));
    }

    /**
     * Completed, non-warm-up sets.
     *
     * @param  Collection<int, SessionSet>  $sets
     * @return Collection<int, SessionSet>
     */
    public function qualifying(Collection $sets): Collection
    {
        return $sets->filter(fn (SessionSet $s) => $s->is_completed && $s->set_type !== 'warmup')->values();
    }

    /** @return array{value: float, set: SessionSet}|null */
    private function bestBy(Collection $sets, callable $metric): ?array
    {
        $best = null;
        foreach ($sets as $set) {
            $v = $metric($set);
            if ($v !== null && ($best === null || $v > $best['value'])) {
                $best = ['value' => round((float) $v, 2), 'set' => $set];
            }
        }

        return $best;
    }

    private function maxOf(Collection $sets, callable $metric): ?float
    {
        $max = null;
        foreach ($sets as $set) {
            $v = $metric($set);
            if ($v !== null && ($max === null || $v > $max)) {
                $max = (float) $v;
            }
        }

        return $max === null ? null : round($max, 2);
    }
}
