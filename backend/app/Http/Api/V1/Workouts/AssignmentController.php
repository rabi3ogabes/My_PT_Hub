<?php

namespace App\Http\Api\V1\Workouts;

use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Services\ProgramAssigner;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Workouts\ProgramAssignmentResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssignmentController extends ApiController
{
    use AuthorizesWorkoutStaff;

    public function __construct(private readonly ProgramAssigner $assigner) {}

    /** GET /assignments?client_id=&program_id=&status=&cursor= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'size:26'],
            'program_id' => ['nullable', 'string', 'size:26'],
            'status' => ['nullable', 'string', 'max:64'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $query = ProgramAssignment::query()
            ->with(['program' => fn ($q) => $q->withTrashed(), 'client'])
            ->when($data['client_id'] ?? null, fn ($q, $id) => $q->where('client_id', $id))
            ->when($data['program_id'] ?? null, fn ($q, $id) => $q->where('program_id', $id))
            ->when($data['status'] ?? null, fn ($q, $s) => $q->whereIn('status', explode(',', $s)))
            ->orderByDesc('created_at')->orderByDesc('id');

        return $this->paginated($query->cursorPaginate($this->perPage())->withQueryString(), ProgramAssignmentResource::class);
    }

    /**
     * PATCH /assignments/{id} {action?: pause|resume, shift_by?: int, shift_days?: int,
     * unlock_mode?, start_date?, status?: completed|cancelled}
     */
    public function update(Request $request, ProgramAssignment $assignment): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $data = $request->validate([
            'action' => ['nullable', Rule::in(['pause', 'resume'])],
            'shift_by' => ['nullable', 'integer', 'min:-365', 'max:365'],
            'shift_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'unlock_mode' => ['nullable', Rule::in(ProgramAssigner::UNLOCK_MODES)],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in(['completed', 'cancelled'])],
        ]);

        DB::transaction(function () use ($assignment, $data) {
            if (($data['action'] ?? null) === 'pause') {
                $this->assigner->pause($assignment);
            } elseif (($data['action'] ?? null) === 'resume') {
                $this->assigner->resume($assignment);
            }
            if (isset($data['shift_days'])) {
                $assignment->forceFill(['shift_days' => $data['shift_days']])->save();
            }
            if (isset($data['shift_by'])) {
                $this->assigner->shift($assignment, (int) $data['shift_by']);
            }
            if (isset($data['unlock_mode'])) {
                $assignment->forceFill(['unlock_mode' => $data['unlock_mode']])->save();
            }
            if (isset($data['start_date'])) {
                $assignment->forceFill(['start_date' => CarbonImmutable::parse($data['start_date'])->toDateString()])->save();
            }
            if (isset($data['status'])) {
                $assignment->forceFill(['status' => $data['status'], 'paused_at' => null])->save();
            }
            $this->assigner->refreshStatus($assignment->refresh());
        });

        return $this->ok(new ProgramAssignmentResource($assignment->refresh()));
    }

    public function destroy(ProgramAssignment $assignment): JsonResponse
    {
        $this->authorizeManage('programs.manage');
        $assignment->delete();

        return $this->noContent();
    }
}
