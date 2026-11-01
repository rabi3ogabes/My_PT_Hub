<?php

namespace App\Http\Api\V1\Workouts;

use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Services\WorkoutBuilder;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Requests\Workouts\WorkoutRequest;
use App\Http\Api\V1\Resources\Workouts\WorkoutResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkoutController extends ApiController
{
    use AuthorizesWorkoutStaff;

    public function __construct(private readonly WorkoutBuilder $builder) {}

    /** GET /workouts?q=&tag=&scope=mine|global|all&template=0|1&cursor= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:40'],
            'scope' => ['nullable', 'in:mine,global,all'],
            'template' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $scope = $data['scope'] ?? 'mine';

        $query = Workout::query()->with($this->with())
            ->when($scope === 'mine', fn ($q) => $q->whereNotNull('tenant_id'))
            ->when($scope === 'global', fn ($q) => $q->whereNull('tenant_id'))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%'))
            ->when($data['tag'] ?? null, fn ($q, $tag) => $q->where('tags', 'like', '%"'.str_replace(['%', '_'], ['\%', '\_'], $tag).'"%'))
            ->when($request->has('template'), fn ($q) => $q->where('is_template', $request->boolean('template')))
            ->orderByDesc('updated_at')->orderByDesc('id');

        return $this->paginated($query->cursorPaginate($this->perPage())->withQueryString(), WorkoutResource::class);
    }

    public function show(Workout $workout): JsonResponse
    {
        return $this->ok(new WorkoutResource($workout->load($this->with())));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $data = $request->validate(WorkoutRequest::rules(true));
        $workout = $this->builder->create($data, $this->user()->id);

        return $this->created(new WorkoutResource($workout->load($this->with())));
    }

    public function update(Request $request, Workout $workout): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $this->builder->assertEditable($workout);
        $data = $request->validate(WorkoutRequest::rules(false));
        $workout = $this->builder->update($workout, $data);

        return $this->ok(new WorkoutResource($workout->load($this->with())));
    }

    public function destroy(Workout $workout): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $this->builder->assertEditable($workout);
        $workout->delete();

        return $this->noContent();
    }

    public function duplicate(Request $request, Workout $workout): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $data = $request->validate(['name' => ['nullable', 'string', 'max:160']]);
        $copy = $this->builder->duplicate($workout, $this->user()->id, $data['name'] ?? null);

        return $this->created(new WorkoutResource($copy->load($this->with())));
    }

    public function export(Workout $workout): JsonResponse
    {
        return $this->ok($this->builder->export($workout));
    }

    /** Body: an exported workout (`format: aurum.workout.v1`) — exercises resolved by id, then slug. */
    public function import(Request $request): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $payload = $request->has('workout') && is_array($request->input('workout')) ? $request->input('workout') : $request->all();
        $validator = validator($payload, [
            'format' => ['nullable', 'in:aurum.workout.v1'],
            ...WorkoutRequest::rules(true),
            'blocks.*.exercises.*.exercise_id' => ['nullable', 'string', 'size:26'],
        ]);
        $data = $this->builder->resolveImport($validator->validate());
        unset($data['format']);
        $workout = $this->builder->create($data, $this->user()->id);

        return $this->created(new WorkoutResource($workout->load($this->with())));
    }

    /** @return array<int|string, mixed> */
    private function with(): array
    {
        return ['blocks.exercises.sets', 'blocks.exercises.exercise' => fn ($q) => $q->withTrashed()];
    }
}
