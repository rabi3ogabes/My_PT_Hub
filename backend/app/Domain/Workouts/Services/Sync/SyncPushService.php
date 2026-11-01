<?php

namespace App\Domain\Workouts\Services\Sync;

use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Nutrition\Models\WaterLog;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Workouts\Models\SyncMutation;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Domain\Workouts\Services\SessionLogger;
use App\Http\Api\V1\Client\SessionController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * POST /sync/push — applies batched offline mutations idempotently.
 * - Each mutation id is recorded once (sync_mutations): replays return `duplicate`.
 * - Entity writes are upserts on the client ULID; session sets are append-only by id.
 * - Conflicts: server timestamps; a mutation older than the row's last server update
 *   only fills fields that are still empty (last write wins per field).
 */
class SyncPushService
{
    public const ENTITIES = ['session', 'session_set', 'food_log', 'water_log', 'habit_log', 'body_metric', 'message'];

    public function __construct(private readonly SessionLogger $logger) {}

    /**
     * @param  list<array<string, mixed>>  $mutations
     * @return list<array{id: string, status: string, error?: string, message?: string}>
     */
    public function push(User $client, array $mutations): array
    {
        $tenantId = app(TenantContext::class)->id();
        $ids = array_map(fn ($m) => strtolower((string) $m['id']), $mutations);
        $seen = SyncMutation::withoutGlobalScopes()->whereIn('id', $ids)->get()->keyBy('id');

        $results = [];
        foreach ($mutations as $mutation) {
            $id = strtolower((string) $mutation['id']);
            if ($seen->has($id)) {
                $row = $seen->get($id);
                $results[] = $row->user_id === $client->id
                    ? ['id' => $id, 'status' => 'duplicate']
                    : ['id' => $id, 'status' => 'rejected', 'error' => 'mutation_conflict'];

                continue;
            }

            try {
                DB::transaction(function () use ($client, $mutation, $id, $tenantId) {
                    $this->apply($client, $mutation);
                    SyncMutation::create([
                        'id' => $id, 'tenant_id' => $tenantId, 'user_id' => $client->id,
                        'entity' => $mutation['entity'], 'op' => $mutation['op'], 'status' => 'applied',
                        'client_time' => isset($mutation['client_time']) ? Carbon::parse($mutation['client_time'])->utc() : null,
                    ]);
                });
                $results[] = ['id' => $id, 'status' => 'applied'];
            } catch (Throwable $e) {
                [$code, $message] = $this->describe($e);
                $results[] = ['id' => $id, 'status' => 'rejected', 'error' => $code, 'message' => $message];
            }
            $seen->put($id, new SyncMutation(['id' => $id, 'user_id' => $client->id]));
        }

        return $results;
    }

    /** @param array<string, mixed> $mutation */
    private function apply(User $client, array $mutation): void
    {
        $payload = (array) ($mutation['payload'] ?? []);
        $op = $mutation['op'];
        $clientTime = isset($mutation['client_time']) ? Carbon::parse($mutation['client_time'])->utc() : null;

        match ($mutation['entity']) {
            'session' => $op === 'delete' ? $this->deleteSession($client, $payload) : $this->upsertSession($client, $payload, $clientTime),
            'session_set' => $op === 'delete' ? $this->deleteSet($client, $payload) : $this->upsertSet($client, $payload),
            'food_log' => $op === 'delete' ? $this->deleteOwned(FoodLog::class, $client, $payload) : $this->upsertFoodLog($client, $payload, $clientTime),
            'water_log' => $op === 'delete' ? $this->deleteOwned(WaterLog::class, $client, $payload) : $this->upsertWaterLog($client, $payload),
            'habit_log' => $op === 'delete' ? $this->deleteHabitLog($client, $payload) : $this->upsertHabitLog($client, $payload),
            'body_metric' => $op === 'delete' ? $this->deleteOwned(BodyMetric::class, $client, $payload) : $this->upsertBodyMetric($client, $payload, $clientTime),
            'message' => throw new ApiException('use_messages_endpoint', __('workouts.use_messages_endpoint'), 422),
        };
    }

    // ------------------------------------------------------------ sessions

    private function upsertSession(User $client, array $payload, ?Carbon $clientTime): void
    {
        $data = validator($payload, [
            'id' => ['required', 'string', 'ulid'],
            'workout_id' => ['nullable', 'string', 'size:26'],
            'program_day_id' => ['nullable', 'string', 'size:26'],
            'program_assignment_id' => ['nullable', 'string', 'size:26'],
            'name' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', Rule::in(['in_progress', 'completed', 'abandoned'])],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'perceived_effort' => ['nullable', 'integer', 'min:1', 'max:10'],
            'mood' => ['nullable', 'string', 'max:16'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ])->validate();

        [$session] = $this->logger->start($client, $data);
        $stale = $clientTime !== null && $session->updated_at !== null && $clientTime->lessThan($session->updated_at) && ! $session->wasRecentlyCreated;
        foreach (['name', 'perceived_effort', 'mood', 'notes'] as $field) {
            if (array_key_exists($field, $payload) && (! $stale || $session->{$field} === null)) {
                $session->{$field} = $data[$field] ?? null;
            }
        }
        if ($session->isDirty()) {
            $session->save();
        }

        $status = $data['status'] ?? null;
        if ($status === 'completed') {
            $this->logger->complete($session, array_intersect_key($data, array_flip(['ended_at', 'perceived_effort', 'mood', 'notes'])));
        } elseif ($status === 'abandoned') {
            $this->logger->abandon($session);
        }
    }

    private function deleteSession(User $client, array $payload): void
    {
        $id = $this->requireId($payload);
        WorkoutSession::query()->where('client_id', $client->id)->whereKey($id)->first()?->delete();
    }

    private function upsertSet(User $client, array $payload): void
    {
        $sessionId = $payload['session_id'] ?? $payload['workout_session_id'] ?? null;
        validator(['session_id' => $sessionId], ['session_id' => ['required', 'string', 'ulid']])->validate();
        $data = validator($payload, SessionController::setRules('', false))->validate();
        $session = WorkoutSession::query()->where('client_id', $client->id)->whereKey(strtolower((string) $sessionId))->first()
            ?? throw new ApiException('session_not_found', __('workouts.session_not_found'), 404);
        $this->logger->upsertSets($session, [$data]);
    }

    private function deleteSet(User $client, array $payload): void
    {
        $id = $this->requireId($payload);
        $sessionId = $payload['session_id'] ?? $payload['workout_session_id'] ?? null;
        $session = WorkoutSession::query()->where('client_id', $client->id)
            ->when($sessionId, fn ($q) => $q->whereKey(strtolower((string) $sessionId)))
            ->whereHas('sets', fn ($q) => $q->whereKey($id))
            ->first();
        if ($session) {
            $this->logger->deleteSet($session, $id);
        }
    }

    // ------------------------------------------------------------ nutrition

    private function upsertFoodLog(User $client, array $payload, ?Carbon $clientTime): void
    {
        $data = validator($payload, [
            'id' => ['required', 'string', 'ulid'],
            'log_date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['required', 'string', 'max:16'],
            'food_id' => ['nullable', 'string', 'size:26'],
            'recipe_id' => ['nullable', 'string', 'size:26'],
            'name' => ['nullable', 'string', 'max:190', 'required_without_all:food_id,recipe_id'],
            'quantity' => ['required', 'numeric', 'min:0', 'max:100000'],
            'unit' => ['nullable', 'string', 'max:16'],
            'kcal' => ['nullable', 'numeric', 'min:0', 'max:100000', 'required_without_all:food_id,recipe_id'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'carbs_g' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'source' => ['nullable', Rule::in(['search', 'barcode', 'quick', 'photo', 'plan', 'copy'])],
            'logged_at' => ['nullable', 'date'],
        ])->validate();

        $macros = $this->foodMacros($data);
        $log = $this->ownedOrNew(FoodLog::class, $client, $data['id']);
        if ($log->exists && $clientTime !== null && $log->updated_at !== null && $clientTime->lessThan($log->updated_at)) {
            return; // an older offline edit never overwrites a newer server row
        }
        $log->fill([
            'client_id' => $client->id,
            'log_date' => $data['log_date'],
            'slot' => $data['slot'],
            'food_id' => $data['food_id'] ?? null,
            'recipe_id' => $data['recipe_id'] ?? null,
            'name' => $data['name'] ?? $macros['name'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'] ?? 'g',
            'kcal' => $macros['kcal'],
            'protein_g' => $macros['protein_g'],
            'carbs_g' => $macros['carbs_g'],
            'fat_g' => $macros['fat_g'],
            'source' => $data['source'] ?? (isset($data['food_id']) || isset($data['recipe_id']) ? 'search' : 'quick'),
            'logged_at' => isset($data['logged_at']) ? Carbon::parse($data['logged_at'])->utc() : ($log->logged_at ?? now()),
        ]);
        if ($log->trashed()) {
            $log->deleted_at = null;
        }
        $log->save();
    }

    /**
     * Macros: computed from the food (per 100 g / serving) or recipe (per serving) when given,
     * otherwise taken from the payload (quick add).
     *
     * @param  array<string, mixed>  $data
     * @return array{kcal: float, protein_g: float, carbs_g: float, fat_g: float, name: ?string}
     */
    private function foodMacros(array $data): array
    {
        $qty = (float) $data['quantity'];
        $unit = $data['unit'] ?? 'g';
        if (! empty($data['food_id'])) {
            $food = Food::query()->find($data['food_id']) ?? throw new ApiException('food_not_found', __('workouts.food_not_found'), 422);
            if ($unit === 'g' || $unit === 'ml') {
                $grams = $qty;
            } else {
                $serving = collect($food->servings ?? [])->first(fn ($s) => ($s['unit'] ?? null) === $unit || $unit === 'serving');
                $grams = $qty * (float) ($serving['grams'] ?? 100);
            }
            $f = $grams / 100;

            return [
                'kcal' => round((float) $food->kcal * $f, 2),
                'protein_g' => round((float) $food->protein_g * $f, 2),
                'carbs_g' => round((float) $food->carbs_g * $f, 2),
                'fat_g' => round((float) $food->fat_g * $f, 2),
                'name' => $food->translate('name'),
            ];
        }
        if (! empty($data['recipe_id'])) {
            $recipe = Recipe::query()->find($data['recipe_id']) ?? throw new ApiException('recipe_not_found', __('workouts.recipe_not_found'), 422);

            return [
                'kcal' => round((float) $recipe->kcal * $qty, 2),
                'protein_g' => round((float) $recipe->protein_g * $qty, 2),
                'carbs_g' => round((float) $recipe->carbs_g * $qty, 2),
                'fat_g' => round((float) $recipe->fat_g * $qty, 2),
                'name' => $recipe->translate('name'),
            ];
        }

        return [
            'kcal' => (float) ($data['kcal'] ?? 0),
            'protein_g' => (float) ($data['protein_g'] ?? 0),
            'carbs_g' => (float) ($data['carbs_g'] ?? 0),
            'fat_g' => (float) ($data['fat_g'] ?? 0),
            'name' => null,
        ];
    }

    private function upsertWaterLog(User $client, array $payload): void
    {
        $data = validator($payload, [
            'id' => ['required', 'string', 'ulid'],
            'log_date' => ['required', 'date_format:Y-m-d'],
            'ml' => ['required', 'integer', 'min:1', 'max:10000'],
            'logged_at' => ['nullable', 'date'],
        ])->validate();
        $log = $this->ownedOrNew(WaterLog::class, $client, $data['id']);
        $log->fill([
            'client_id' => $client->id,
            'log_date' => $data['log_date'],
            'ml' => $data['ml'],
            'logged_at' => isset($data['logged_at']) ? Carbon::parse($data['logged_at'])->utc() : ($log->logged_at ?? now()),
        ])->save();
    }

    // ------------------------------------------------------------ habits & progress

    private function upsertHabitLog(User $client, array $payload): void
    {
        $data = validator($payload, [
            'id' => ['nullable', 'string', 'ulid'],
            'assignment_id' => ['required', 'string', 'size:26'],
            'date' => ['required', 'date_format:Y-m-d'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'completed' => ['nullable', 'boolean'],
        ])->validate();
        $assignment = HabitAssignment::query()->where('client_id', $client->id)->whereKey($data['assignment_id'])->with('habit')->first()
            ?? throw new ApiException('habit_assignment_not_found', __('workouts.habit_assignment_not_found'), 404);

        $log = HabitLog::query()->where('habit_assignment_id', $assignment->id)->whereDate('log_date', $data['date'])->first();
        if (! $log) {
            $log = new HabitLog(['habit_assignment_id' => $assignment->id, 'log_date' => $data['date']]);
            if (! empty($data['id']) && ! HabitLog::query()->whereKey(strtolower($data['id']))->exists()) {
                $log->id = strtolower($data['id']);
            }
        }
        $value = $data['value'] ?? ($log->exists ? $log->value : 1);
        $target = $assignment->target ?? $assignment->habit?->target;
        $completed = $data['completed'] ?? ($target !== null && ($assignment->habit?->type ?? 'boolean') !== 'boolean' ? (float) $value >= (float) $target : true);
        $log->fill(['value' => $value, 'completed' => (bool) $completed, 'source' => 'manual'])->save();
    }

    private function deleteHabitLog(User $client, array $payload): void
    {
        $assignmentIds = HabitAssignment::query()->where('client_id', $client->id)->pluck('id');
        $query = HabitLog::query()->whereIn('habit_assignment_id', $assignmentIds);
        if (! empty($payload['id'])) {
            $query->whereKey(strtolower((string) $payload['id']));
        } elseif (! empty($payload['assignment_id']) && ! empty($payload['date'])) {
            $query->where('habit_assignment_id', $payload['assignment_id'])->whereDate('log_date', $payload['date']);
        } else {
            throw ValidationException::withMessages(['id' => [__('validation.required', ['attribute' => 'id'])]]);
        }
        $query->delete();
    }

    private function upsertBodyMetric(User $client, array $payload, ?Carbon $clientTime): void
    {
        $data = validator($payload, [
            'id' => ['required', 'string', 'ulid'],
            'measured_on' => ['required', 'date_format:Y-m-d'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:400'],
            'body_fat_pct' => ['nullable', 'numeric', 'min:2', 'max:75'],
            'measurements' => ['nullable', 'array', 'max:20'],
            'measurements.*' => ['nullable', 'numeric', 'min:0', 'max:400'],
        ])->validate();
        $metric = $this->ownedOrNew(BodyMetric::class, $client, $data['id']);
        $stale = $metric->exists && $clientTime !== null && $metric->updated_at !== null && $clientTime->lessThan($metric->updated_at);
        $metric->client_id = $client->id;
        $metric->measured_on = $data['measured_on'];
        foreach (['weight_kg', 'body_fat_pct', 'measurements'] as $field) {
            if (array_key_exists($field, $data) && (! $stale || $metric->{$field} === null)) {
                $metric->{$field} = $data[$field];
            }
        }
        $metric->source ??= 'manual';
        $metric->save();
    }

    // ------------------------------------------------------------ helpers

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function ownedOrNew(string $class, User $client, string $id): Model
    {
        $id = strtolower($id);
        $any = $class::withoutGlobalScopes()->whereKey($id)->first();
        if ($any) {
            $tenantId = app(TenantContext::class)->id();
            if ($any->getAttribute('client_id') !== $client->id || $any->getAttribute('tenant_id') !== $tenantId) {
                throw ApiException::conflict('id_conflict', __('workouts.id_conflict'));
            }

            return $any;
        }
        $model = new $class;
        $model->id = $id;

        return $model;
    }

    /** @param class-string<Model> $class */
    private function deleteOwned(string $class, User $client, array $payload): void
    {
        $id = $this->requireId($payload);
        $class::query()->where('client_id', $client->id)->whereKey($id)->first()?->delete();
    }

    private function requireId(array $payload): string
    {
        $id = $payload['id'] ?? null;
        validator(['id' => $id], ['id' => ['required', 'string', 'ulid']])->validate();

        return strtolower((string) $id);
    }

    /** @return array{0: string, 1: string} */
    private function describe(Throwable $e): array
    {
        return match (true) {
            $e instanceof ApiException => [$e->errorCode, $e->getMessage()],
            $e instanceof ValidationException => ['validation_failed', collect($e->errors())->flatten()->first() ?? __('api.validation_failed')],
            $e instanceof ModelNotFoundException => ['not_found', __('api.not_found')],
            default => tap(['server_error', __('api.server_error')], fn () => report($e)),
        };
    }
}
