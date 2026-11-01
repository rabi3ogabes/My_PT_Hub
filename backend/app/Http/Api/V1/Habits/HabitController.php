<?php

namespace App\Http\Api\V1\Habits;

use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Services\HabitService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Habits\HabitAssignmentResource;
use App\Http\Api\V1\Resources\Habits\HabitResource;
use App\Http\Api\V1\Support\TenantClients;
use App\Http\Api\V1\Support\TranslatableInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HabitController extends ApiController
{
    public const TYPES = ['boolean', 'count', 'duration', 'numeric'];

    public function __construct(private readonly HabitService $habits) {}

    /** GET /habits — global templates + tenant custom; ?client_id= adds that client's assignments in meta. */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['client_id' => ['nullable', 'string', 'size:26'], 'q' => ['nullable', 'string', 'max:64']]);
        $habits = Habit::query()->orderByRaw('tenant_id is not null')->orderBy('created_at')->get();
        if (! empty($filters['q'])) {
            $q = mb_strtolower($filters['q']);
            $habits = $habits->filter(fn (Habit $h) => collect($h->name ?? [])->contains(fn ($v) => str_contains(mb_strtolower((string) $v), $q)))->values();
        }

        $meta = [];
        if (! empty($filters['client_id'])) {
            $client = TenantClients::find([$filters['client_id']], 'client_id')[0];
            $today = $this->habits->today($client);
            $meta['assignments'] = HabitAssignment::query()->where('client_id', $client->id)->where('is_active', true)
                ->with(['habit', 'logs'])->get()
                ->map(fn (HabitAssignment $a) => (new HabitAssignmentResource($a))->resolve($request) + ['progress' => $this->habits->item($a, $today)])
                ->all();
        }

        return $this->ok(HabitResource::collection($habits)->resolve($request), $meta);
    }

    /** POST /habits — tenant custom habit. */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate([
            ...TranslatableInput::rules('name', true, 120),
            'icon' => ['nullable', 'string', 'max:32'],
            'type' => ['required', Rule::in(self::TYPES)],
            'target' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'unit' => ['nullable', 'string', 'max:16'],
        ]);
        $habit = Habit::create([
            'tenant_id' => $this->tenant()->id,
            'name' => TranslatableInput::value($data['name']),
            'icon' => $data['icon'] ?? 'check',
            'type' => $data['type'],
            'target' => $data['target'] ?? null,
            'unit' => $data['unit'] ?? null,
        ]);

        return $this->created(new HabitResource($habit));
    }

    /** POST /habit-assignments {habit_id, client_ids[], schedule, days?, per_week?, target?, reminder_times?, start_date?, end_date?} */
    public function assign(Request $request): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate([
            'habit_id' => ['required', 'string', 'size:26'],
            'client_ids' => ['required_without:client_id', 'array', 'max:200'],
            'client_ids.*' => ['string', 'size:26'],
            'client_id' => ['nullable', 'string', 'size:26'],
            ...$this->scheduleRules(true),
        ]);
        $habit = Habit::query()->findOrFail($data['habit_id']);
        $clients = TenantClients::find($data['client_ids'] ?? [$data['client_id']]);

        $created = DB::transaction(fn () => collect($clients)->map(fn ($client) => HabitAssignment::create([
            'habit_id' => $habit->id,
            'client_id' => $client->id,
            ...$this->scheduleAttributes($data),
            'start_date' => $data['start_date'] ?? $this->habits->today($client),
            'end_date' => $data['end_date'] ?? null,
            'is_active' => true,
        ])->load('habit')));

        return $this->created(HabitAssignmentResource::collection($created)->resolve($request));
    }

    public function updateAssignment(Request $request, HabitAssignment $assignment): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate([...$this->scheduleRules(false), 'is_active' => ['sometimes', 'boolean']]);
        $attributes = $this->scheduleAttributes($data, $assignment);
        foreach (['start_date', 'end_date', 'is_active'] as $key) {
            if (array_key_exists($key, $data)) {
                $attributes[$key] = $data[$key];
            }
        }
        $assignment->update($attributes);

        return $this->ok(new HabitAssignmentResource($assignment->refresh()->load('habit')));
    }

    /** DELETE /habit-assignments/{id} — deactivates (history and streaks are kept). */
    public function unassign(HabitAssignment $assignment): JsonResponse
    {
        $this->authorizeManage();
        $assignment->update(['is_active' => false, 'end_date' => $assignment->end_date ?? now()->toDateString()]);

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function scheduleRules(bool $creating): array
    {
        return [
            'schedule' => [$creating ? 'required' : 'sometimes', Rule::in(['daily', 'days', 'per_week'])],
            'days' => ['nullable', 'required_if:schedule,days', 'array', 'min:1', 'max:7'],
            'days.*' => ['integer', 'between:1,7', 'distinct'],
            'per_week' => ['nullable', 'required_if:schedule,per_week', 'integer', 'between:1,7'],
            'target' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'reminder_times' => ['nullable', 'array', 'max:6'],
            'reminder_times.*' => ['date_format:H:i'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function scheduleAttributes(array $data, ?HabitAssignment $existing = null): array
    {
        $out = [];
        if (array_key_exists('schedule', $data)) {
            $out['schedule'] = $data['schedule'];
            $out['days'] = $data['schedule'] === 'days' ? array_values(array_map('intval', $data['days'] ?? [])) : null;
            $out['per_week'] = $data['schedule'] === 'per_week' ? (int) $data['per_week'] : null;
        }
        foreach (['target', 'reminder_times'] as $key) {
            if (array_key_exists($key, $data)) {
                $out[$key] = $data[$key];
            }
        }
        if (! $existing) {
            $out += ['target' => null, 'reminder_times' => null];
        }

        return $out;
    }

    private function authorizeManage(): void
    {
        if (! $this->user()->can('habits.manage')) {
            throw ApiException::forbidden();
        }
    }
}
