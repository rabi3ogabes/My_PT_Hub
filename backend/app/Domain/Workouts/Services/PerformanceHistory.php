<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutSession;
use Illuminate\Support\Collection;

/** Read models for the logger: last performance (prefill), exercise history, records board. */
class PerformanceHistory
{
    /**
     * Sets from the most recent session in which the client logged the exercise.
     *
     * @return array{session_id: string, performed_at: string, sets: list<array<string, mixed>>}|null
     */
    public function last(User $client, string $exerciseId, ?string $excludeSessionId = null): ?array
    {
        return $this->lastMany($client, [$exerciseId], $excludeSessionId)[$exerciseId] ?? null;
    }

    /**
     * @param  list<string>  $exerciseIds
     * @return array<string, array{session_id: string, performed_at: string, sets: list<array<string, mixed>>}>
     */
    public function lastMany(User $client, array $exerciseIds, ?string $excludeSessionId = null): array
    {
        if ($exerciseIds === []) {
            return [];
        }
        $sessions = WorkoutSession::query()
            ->where('client_id', $client->id)
            ->where('status', '!=', 'abandoned')
            ->when($excludeSessionId, fn ($q) => $q->where('id', '!=', $excludeSessionId))
            ->whereHas('sets', fn ($q) => $q->whereIn('exercise_id', $exerciseIds)->where('is_completed', true))
            ->orderByDesc('started_at')
            ->limit(60)
            ->get(['id', 'started_at', 'ended_at'])
            ->keyBy('id');
        if ($sessions->isEmpty()) {
            return [];
        }

        $sets = SessionSet::query()
            ->whereIn('workout_session_id', $sessions->keys())
            ->whereIn('exercise_id', $exerciseIds)
            ->where('is_completed', true)
            ->orderBy('set_number')->orderBy('completed_at')
            ->get()
            ->groupBy('exercise_id');

        $out = [];
        foreach ($sets as $exerciseId => $exerciseSets) {
            $latestSessionId = $exerciseSets->pluck('workout_session_id')->unique()
                ->sortByDesc(fn ($id) => $sessions->get($id)?->started_at?->getTimestamp() ?? 0)->first();
            $session = $sessions->get($latestSessionId);
            $out[$exerciseId] = [
                'session_id' => $latestSessionId,
                'performed_at' => ($session->ended_at ?? $session->started_at)->toIso8601String(),
                'sets' => $exerciseSets->where('workout_session_id', $latestSessionId)->values()->map(fn (SessionSet $s) => [
                    'set_number' => $s->set_number,
                    'reps' => $s->reps,
                    'weight_kg' => $s->weight_kg,
                    'duration_seconds' => $s->duration_seconds,
                    'distance_m' => $s->distance_m,
                    'rpe' => $s->rpe,
                ])->all(),
            ];
        }

        return $out;
    }

    /**
     * @return array{best_e1rm_kg: ?float, best_weight_kg: ?float, sessions: list<array<string, mixed>>}
     */
    public function history(User $client, string $exerciseId, int $limit = 30): array
    {
        $sessions = WorkoutSession::query()
            ->where('client_id', $client->id)
            ->where('status', 'completed')
            ->whereHas('sets', fn ($q) => $q->where('exercise_id', $exerciseId))
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get(['id', 'started_at', 'client_id']);

        $sets = SessionSet::query()
            ->whereIn('workout_session_id', $sessions->pluck('id'))
            ->where('exercise_id', $exerciseId)
            ->where('is_completed', true)->where('set_type', '!=', 'warmup')
            ->get()->groupBy('workout_session_id');

        $rows = $sessions->map(function (WorkoutSession $session) use ($sets, $client) {
            /** @var Collection<int, SessionSet> $s */
            $s = $sets->get($session->id, collect());
            $top = $s->sortByDesc(fn (SessionSet $x) => [OneRepMax::epley($x->weight_kg, $x->reps) ?? 0, (float) ($x->weight_kg ?? 0), (int) ($x->reps ?? 0)])->first();
            $e1rm = $s->map(fn (SessionSet $x) => OneRepMax::epley($x->weight_kg, $x->reps))->filter()->max();

            return [
                'session_id' => $session->id,
                'date' => SessionLogger::localDate($session, $client),
                'top_set' => $top ? ['reps' => $top->reps, 'weight_kg' => $top->weight_kg] : null,
                'volume_kg' => round((float) $s->sum(fn (SessionSet $x) => (float) ($x->weight_kg ?? 0) * (int) ($x->reps ?? 0)), 2),
                'e1rm_kg' => $e1rm !== null ? round((float) $e1rm, 2) : null,
            ];
        })->values();

        $bests = PersonalRecord::query()->where('client_id', $client->id)->where('exercise_id', $exerciseId)
            ->whereIn('type', ['e1rm', 'weight'])->selectRaw('type, max(value) as best')->groupBy('type')->pluck('best', 'type');

        $bestE1rm = $bests['e1rm'] ?? $rows->max('e1rm_kg');
        $bestWeight = $bests['weight'] ?? $rows->map(fn ($r) => $r['top_set']['weight_kg'] ?? null)->filter()->max();

        return [
            'best_e1rm_kg' => $bestE1rm !== null ? round((float) $bestE1rm, 2) : null,
            'best_weight_kg' => $bestWeight !== null ? round((float) $bestWeight, 2) : null,
            'sessions' => $rows->all(),
        ];
    }

    /**
     * Best record per exercise and type.
     *
     * @return Collection<int, PersonalRecord>
     */
    public function recordsBoard(User $client): Collection
    {
        return PersonalRecord::query()
            ->where('client_id', $client->id)
            ->with(['exercise' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('value')->orderByDesc('achieved_at')
            ->get()
            ->unique(fn (PersonalRecord $r) => $r->exercise_id.'|'.$r->type)
            ->sortByDesc('achieved_at')
            ->values();
    }
}
