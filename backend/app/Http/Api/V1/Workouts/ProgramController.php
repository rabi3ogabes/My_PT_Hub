<?php

namespace App\Http\Api\V1\Workouts;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramWeek;
use App\Domain\Workouts\Services\ProgramAssigner;
use App\Domain\Workouts\Services\ProgramBuilder;
use App\Domain\Workouts\Services\ProgramSchedule;
use App\Domain\Workouts\Support\Translatable;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Workouts\ProgramAssignmentResource;
use App\Http\Api\V1\Resources\Workouts\ProgramResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgramController extends ApiController
{
    use AuthorizesWorkoutStaff;

    public function __construct(
        private readonly ProgramBuilder $builder,
        private readonly ProgramAssigner $assigner,
    ) {}

    /** GET /programs?q=&scope=mine|global|all&template=0|1&goal=&level=&cursor= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'scope' => ['nullable', 'in:mine,global,all'],
            'template' => ['nullable', 'boolean'],
            'goal' => ['nullable', 'string', 'max:32'],
            'level' => ['nullable', 'string', 'max:16'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $scope = $data['scope'] ?? 'mine';
        $query = Program::query()->with(ProgramBuilder::WITH)
            ->when($scope === 'mine', fn ($q) => $q->whereNotNull('tenant_id'))
            ->when($scope === 'global', fn ($q) => $q->whereNull('tenant_id')->where('visibility', 'public'))
            ->when($scope === 'all', fn ($q) => $q->where(fn ($w) => $w->whereNotNull('tenant_id')->orWhere('visibility', 'public')))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->whereRaw('lower('.($q->getConnection()->getDriverName() === 'pgsql' ? 'name::text' : 'name').') like ?', ['%'.mb_strtolower(str_replace(['%', '_'], '', $term)).'%']))
            ->when($data['goal'] ?? null, fn ($q, $goal) => $q->where('goal', $goal))
            ->when($data['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
            ->when($request->has('template'), fn ($q) => $q->where('is_template', $request->boolean('template')))
            ->orderByDesc('updated_at')->orderByDesc('id');

        $page = $query->cursorPaginate($this->perPage())->withQueryString();
        $items = collect($page->items())->map(fn (Program $p) => (new ProgramResource($p))->withTranslations()->toArray($request))->all();

        return response()->json([
            'data' => $items,
            'meta' => ['per_page' => $page->perPage(), 'next_cursor' => $page->nextCursor()?->encode(), 'prev_cursor' => $page->previousCursor()?->encode()],
            'links' => ['next' => $page->nextPageUrl(), 'prev' => $page->previousPageUrl()],
            'errors' => [],
        ]);
    }

    /** Public (unauthenticated) listing of platform template programs. */
    public function publicTemplates(Request $request): JsonResponse
    {
        $data = $request->validate([
            'goal' => ['nullable', 'string', 'max:32'],
            'level' => ['nullable', 'string', 'max:16'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $page = Program::query()->withoutGlobalScopes()
            ->whereNull('tenant_id')->whereNull('deleted_at')
            ->where('visibility', 'public')->where('is_template', true)
            ->with(['weeks.days.workout' => fn ($q) => $q->withoutGlobalScopes()])
            ->when($data['goal'] ?? null, fn ($q, $goal) => $q->where('goal', $goal))
            ->when($data['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
            ->orderBy('slug')->orderBy('id')
            ->cursorPaginate($this->perPage())->withQueryString();

        return $this->paginated($page, ProgramResource::class);
    }

    public function show(Request $request, Program $program): JsonResponse
    {
        $this->assertVisible($program);

        return $this->ok((new ProgramResource($program->load(ProgramBuilder::WITH)))->withTranslations()->toArray($request));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $program = $this->builder->create($this->validated($request, true), $this->user()->id);

        return $this->created((new ProgramResource($program))->withTranslations()->toArray($request));
    }

    public function update(Request $request, Program $program): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $this->builder->assertEditable($program);
        $program = $this->builder->update($program, $this->validated($request, false));

        return $this->ok((new ProgramResource($program))->withTranslations()->toArray($request));
    }

    public function destroy(Program $program): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $this->builder->assertEditable($program);
        DB::transaction(function () use ($program) {
            $program->assignments()->whereIn('status', ['active', 'paused', 'scheduled'])->update(['status' => 'cancelled']);
            $program->delete();
        });

        return $this->noContent();
    }

    public function duplicate(Request $request, Program $program): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $this->assertVisible($program);
        $copy = $this->builder->duplicate($program, $this->user()->id);

        return $this->created((new ProgramResource($copy))->withTranslations()->toArray($request));
    }

    /** POST /programs/{program}/weeks/{week}/copy {to_week?} — omit to append a new week. */
    public function copyWeek(Request $request, Program $program, ProgramWeek $week): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $data = $request->validate(['to_week' => ['nullable', 'integer', 'min:1', 'max:520']]);
        $program = $this->builder->copyWeek($program, $week, $data['to_week'] ?? null);

        return $this->ok((new ProgramResource($program))->withTranslations()->toArray($request));
    }

    /** POST /programs/{program}/assign {client_ids, start_date, unlock_mode?} → [ProgramAssignment] */
    public function assign(Request $request, Program $program): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $this->assertVisible($program);
        $data = $request->validate([
            'client_ids' => ['required', 'array', 'min:1', 'max:200'],
            'client_ids.*' => ['string', 'size:26', 'distinct'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'unlock_mode' => ['nullable', Rule::in(ProgramAssigner::UNLOCK_MODES)],
        ]);
        $tenantId = $this->tenant()->id;
        $clients = User::query()->whereIn('id', $data['client_ids'])
            ->whereHas('memberships', fn ($q) => $q->where('tenant_id', $tenantId)->where('role', 'client')->where('status', 'active'))
            ->get()->keyBy('id');
        $missing = array_values(array_diff($data['client_ids'], $clients->keys()->all()));
        if ($missing !== []) {
            throw ApiException::invalid('validation_failed', __('workouts.not_a_client'), ['client_ids' => [__('workouts.not_a_client')]]);
        }

        $assignments = DB::transaction(fn () => collect($data['client_ids'])->map(fn (string $id) => $this->assigner->assign(
            $program,
            $clients->get($id),
            $data['start_date'] ?? ProgramSchedule::today($clients->get($id))->toDateString(),
            $this->user()->id,
            $data['unlock_mode'] ?? 'all',
        )));

        return $this->created(ProgramAssignmentResource::collection($assignments)->resolve($request));
    }

    private function assertVisible(Program $program): void
    {
        if ($program->tenant_id === null && $program->visibility !== 'public' && ! $this->user()->is_super_admin) {
            throw new ModelNotFoundException;
        }
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $visibilities = $this->user()->is_super_admin ? ProgramBuilder::VISIBILITIES : ['private', 'tenant_template'];

        return $request->validate([
            'name' => [$creating ? 'required' : 'sometimes', Translatable::rule(160)],
            'description' => ['sometimes', 'nullable', Translatable::rule(5000)],
            'goal' => ['sometimes', 'nullable', 'string', 'max:32'],
            'level' => ['sometimes', Rule::in(ProgramBuilder::LEVELS)],
            'days_per_week' => ['sometimes', 'integer', 'min:1', 'max:7'],
            'equipment' => ['sometimes', 'nullable', 'array', 'max:30'],
            'equipment.*' => ['string', 'max:32'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'visibility' => ['sometimes', Rule::in($visibilities)],
            'is_template' => ['sometimes', 'boolean'],
            'cover_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'weeks' => [$creating ? 'present' : 'sometimes', 'array', 'max:104'],
            'weeks.*.id' => ['sometimes', 'nullable', 'string', 'size:26'],
            'weeks.*.number' => ['sometimes', 'nullable', 'integer'],
            'weeks.*.phase' => ['nullable', Rule::in(ProgramBuilder::PHASES)],
            'weeks.*.is_deload' => ['nullable', 'boolean'],
            'weeks.*.notes' => ['nullable', 'string', 'max:2000'],
            'weeks.*.days' => ['present', 'array', 'max:14'],
            'weeks.*.days.*.id' => ['sometimes', 'nullable', 'string', 'size:26'],
            'weeks.*.days.*.day_number' => ['required', 'integer', 'min:1', 'max:7'],
            'weeks.*.days.*.title' => ['nullable', 'string', 'max:120'],
            'weeks.*.days.*.is_rest' => ['nullable', 'boolean'],
            'weeks.*.days.*.workout_id' => ['nullable', 'string', 'size:26'],
            'weeks.*.days.*.meal_plan_id' => ['nullable', 'string', 'size:26'],
            'weeks.*.days.*.habit_ids' => ['nullable', 'array', 'max:20'],
            'weeks.*.days.*.habit_ids.*' => ['string', 'size:26'],
        ]);
    }
}
