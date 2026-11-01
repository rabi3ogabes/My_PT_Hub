<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Domain\Workouts\Services\PerformanceHistory;
use App\Domain\Workouts\Services\SessionLogger;
use App\Domain\Workouts\Services\WorkoutBuilder;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Workouts\PersonalRecordResource;
use App\Http\Api\V1\Resources\Workouts\SessionResource;
use App\Http\Api\V1\Resources\Workouts\SessionSetResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The client's own workout sessions (offline-first logger). */
class SessionController extends ApiController
{
    public function __construct(
        private readonly SessionLogger $logger,
        private readonly PerformanceHistory $history,
    ) {}

    /** GET /me/sessions?status=&cursor= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(['in_progress', 'completed', 'abandoned'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $page = WorkoutSession::query()
            ->where('client_id', $this->user()->id)
            ->when($data['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->with(['sets', 'personalRecords.exercise' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('started_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage(20))->withQueryString();

        return $this->paginated($page, SessionResource::class);
    }

    public function show(WorkoutSession $session): JsonResponse
    {
        return $this->ok(new SessionResource($this->own($session)));
    }

    /** POST /me/sessions — idempotent on the client ULID `id`. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'string', 'ulid'],
            'workout_id' => ['nullable', 'string', 'size:26'],
            'program_day_id' => ['nullable', 'string', 'size:26'],
            'program_assignment_id' => ['nullable', 'string', 'size:26'],
            'name' => ['nullable', 'string', 'max:160'],
            'started_at' => ['nullable', 'date'],
        ]);
        [$session, $created] = $this->logger->start($this->user(), $data);

        return $this->ok(new SessionResource($session->refresh()), [], $created ? 201 : 200);
    }

    /** POST /me/sessions/{id}/sets {sets: [SessionSet]} — batch upsert by set id. */
    public function sets(Request $request, WorkoutSession $session): JsonResponse
    {
        $session = $this->own($session);
        $data = $request->validate(self::setRules('sets.*.'));
        $sets = $this->logger->upsertSets($session, $data['sets']);

        return $this->ok(['sets' => array_map(fn ($s) => SessionSetResource::shape($s), $sets)]);
    }

    /** POST /me/sessions/{id}/complete — idempotent. */
    public function complete(Request $request, WorkoutSession $session): JsonResponse
    {
        $session = $this->own($session);
        $data = $request->validate([
            'ended_at' => ['nullable', 'date'],
            'perceived_effort' => ['nullable', 'integer', 'min:1', 'max:10'],
            'mood' => ['nullable', 'string', 'max:16'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $session = $this->logger->complete($session, $data);

        return $this->ok(new SessionResource($session->refresh()));
    }

    public function exerciseHistory(Exercise $exercise): JsonResponse
    {
        return $this->ok($this->history->history($this->user(), $exercise->id));
    }

    public function lastPerformance(Exercise $exercise): JsonResponse
    {
        return $this->ok($this->history->last($this->user(), $exercise->id));
    }

    public function records(Request $request): JsonResponse
    {
        return $this->ok(PersonalRecordResource::collection($this->history->recordsBoard($this->user()))->resolve($request));
    }

    /** @return array<string, mixed> */
    public static function setRules(string $prefix, bool $batch = true): array
    {
        $rules = [
            $prefix.'id' => ['required', 'string', 'ulid'],
            $prefix.'exercise_id' => ['required', 'string', 'size:26'],
            $prefix.'workout_exercise_id' => ['nullable', 'string', 'size:26'],
            $prefix.'set_number' => ['nullable', 'integer', 'min:1', 'max:200'],
            $prefix.'set_type' => ['nullable', Rule::in(WorkoutBuilder::SET_TYPES)],
            $prefix.'reps' => ['nullable', 'integer', 'min:0', 'max:1000'],
            $prefix.'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            $prefix.'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:65535'],
            $prefix.'distance_m' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            $prefix.'rpe' => ['nullable', 'numeric', 'min:1', 'max:10'],
            $prefix.'is_completed' => ['nullable', 'boolean'],
            $prefix.'completed_at' => ['nullable', 'date'],
        ];

        return $batch ? ['sets' => ['required', 'array', 'min:1', 'max:200'], ...$rules] : $rules;
    }

    /** Clients only ever see their own sessions (staff use the coach endpoints). */
    private function own(WorkoutSession $session): WorkoutSession
    {
        if ($session->client_id !== $this->user()->id) {
            throw new ModelNotFoundException;
        }

        return $session;
    }
}
