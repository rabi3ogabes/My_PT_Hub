<?php

namespace App\Http\Api\V1\Checkins;

use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Checkins\Services\CheckinService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Checkins\CheckinFormResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckinFormController extends ApiController
{
    public function __construct(private readonly CheckinService $checkins) {}

    public function index(): JsonResponse
    {
        return $this->ok(CheckinFormResource::collection(CheckinForm::query()->orderBy('created_at')->get())->resolve());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate($this->rules(true));
        $form = CheckinForm::create([
            'name' => $data['name'],
            'intro' => $data['intro'] ?? null,
            'questions' => $this->checkins->normalizeQuestions($data['questions']),
            'include_metrics' => $data['include_metrics'] ?? true,
            'include_photos' => $data['include_photos'] ?? true,
        ]);

        return $this->created(new CheckinFormResource($form));
    }

    public function show(CheckinForm $checkinForm): JsonResponse
    {
        return $this->ok(new CheckinFormResource($checkinForm), [
            'scheduled_clients' => CheckinSchedule::query()->where('checkin_form_id', $checkinForm->id)->where('is_active', true)->count(),
        ]);
    }

    public function update(Request $request, CheckinForm $checkinForm): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate($this->rules(false));
        if (array_key_exists('questions', $data)) {
            $data['questions'] = $this->checkins->normalizeQuestions($data['questions']);
        }
        $checkinForm->update($data);

        return $this->ok(new CheckinFormResource($checkinForm->refresh()));
    }

    /** Soft-deletes the form and deactivates its schedules (existing responses are kept). */
    public function destroy(CheckinForm $checkinForm): JsonResponse
    {
        $this->authorizeManage();
        CheckinSchedule::query()->where('checkin_form_id', $checkinForm->id)->update(['is_active' => false]);
        $checkinForm->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return [
            'name' => [$req, 'string', 'max:190'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'questions' => [$req, 'array', 'min:1', 'max:50'],
            'include_metrics' => ['sometimes', 'boolean'],
            'include_photos' => ['sometimes', 'boolean'],
        ];
    }

    private function authorizeManage(): void
    {
        $user = $this->user();
        if (! $user->can('checkins.review') || $user->roleIn() === 'assistant') {
            throw ApiException::forbidden();
        }
    }
}
