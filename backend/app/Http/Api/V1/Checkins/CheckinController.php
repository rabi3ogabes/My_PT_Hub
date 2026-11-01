<?php

namespace App\Http\Api\V1\Checkins;

use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Checkins\Services\CheckinService;
use App\Domain\Identity\Models\User;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Progress\Services\ComplianceService;
use App\Domain\Progress\Services\ProgressService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Checkins\CheckinFormResource;
use App\Http\Api\V1\Resources\Checkins\CheckinResponseResource;
use App\Http\Api\V1\Support\TenantClients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CheckinController extends ApiController
{
    public function __construct(
        private readonly CheckinService $checkins,
        private readonly ProgressService $progress,
        private readonly ComplianceService $compliance,
    ) {}

    /** POST /checkin-schedules {form_id, client_ids[], frequency, weekday?, every_days?, due_time?, start_date?} */
    public function schedule(Request $request): JsonResponse
    {
        $this->authorizeReview();
        $data = $request->validate([
            'form_id' => ['required', 'string', 'size:26'],
            'client_ids' => ['required', 'array', 'min:1', 'max:200'],
            'client_ids.*' => ['string', 'size:26'],
            'frequency' => ['required', Rule::in(['weekly', 'every_n_days', 'program_week_end'])],
            'weekday' => ['nullable', 'integer', 'between:1,7'],
            'every_days' => ['nullable', 'required_if:frequency,every_n_days', 'integer', 'between:1,90'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $form = CheckinForm::query()->findOrFail($data['form_id']);
        $clients = TenantClients::find($data['client_ids']);
        $schedules = DB::transaction(fn () => collect($clients)->map(fn (User $c) => $this->checkins->schedule($form, $c, $data)));

        return $this->created($schedules->map(fn (CheckinSchedule $s) => $this->schedulePayload($s))->values()->all());
    }

    public function unschedule(CheckinSchedule $schedule): JsonResponse
    {
        $this->authorizeReview();
        $schedule->update(['is_active' => false]);

        return $this->noContent();
    }

    /** GET /checkins?filter[status]=unread|overdue|reviewed|pending|submitted&client_id=&cursor= */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'filter.status' => ['nullable', Rule::in(['unread', 'overdue', 'reviewed', 'pending', 'submitted', 'all'])],
            'client_id' => ['nullable', 'string', 'size:26'],
        ]);
        $status = $filters['filter']['status'] ?? 'all';
        $query = CheckinResponse::query()
            ->with(['client', 'form' => fn ($q) => $q->withTrashed()])
            ->withCount('comments')
            ->when($filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($status === 'unread' || $status === 'submitted', fn ($q) => $q->where('status', 'submitted'))
            ->when(in_array($status, ['overdue', 'reviewed', 'pending'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id');

        $counts = CheckinResponse::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        return $this->paginated($query->cursorPaginate($this->perPage()), CheckinResponseResource::class, [
            'counts' => [
                'unread' => (int) ($counts['submitted'] ?? 0),
                'overdue' => (int) ($counts['overdue'] ?? 0),
                'pending' => (int) ($counts['pending'] ?? 0),
                'reviewed' => (int) ($counts['reviewed'] ?? 0),
            ],
        ]);
    }

    /** GET /checkins/{response} — response with full review context. */
    public function show(CheckinResponse $response): JsonResponse
    {
        return $this->ok($this->detail($response));
    }

    /** POST /checkins/{response}/review {feedback?} */
    public function review(Request $request, CheckinResponse $response): JsonResponse
    {
        $this->authorizeReview();
        $data = $request->validate(['feedback' => ['nullable', 'string', 'max:5000']]);
        $this->checkins->review($response, $this->user(), $data['feedback'] ?? null);

        return $this->ok($this->detail($response->refresh()));
    }

    /** POST /checkins/{response}/comments {body} */
    public function comment(Request $request, CheckinResponse $response): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $comment = $this->checkins->comment($response, $this->user(), $data['body']);

        return $this->created(CheckinResponseResource::comments([$comment])[0]);
    }

    /** GET /clients/{client}/progress-photos/{photo} — signed, short-lived staff access. */
    public function photo(Request $request, User $client, ProgressPhoto $photo): Response
    {
        if (! $request->hasValidSignature()) {
            throw ApiException::forbidden(__('progress.errors.invalid_signature'));
        }
        TenantClients::assert($client);
        if ($photo->client_id !== $client->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $disk = Storage::disk($photo->disk ?: ProgressService::DISK);
        if (! $disk->exists($photo->path)) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return $disk->response($photo->path, null, ['Cache-Control' => 'private, max-age=600']);
    }

    /** @return array<string, mixed> */
    private function detail(CheckinResponse $response): array
    {
        $response->load(['form' => fn ($q) => $q->withTrashed(), 'client', 'comments.user', 'photos', 'reviewer']);
        $client = $response->client;
        $form = $response->form;
        $metrics = $client ? BodyMetric::query()->where('client_id', $client->id)->orderByDesc('measured_on')->limit(6)->get() : collect();
        $previous = $this->checkins->previous($response);
        $previousPhotos = $previous->isNotEmpty()
            ? ProgressPhoto::query()->where('checkin_response_id', $previous->first()->id)->get()
            : collect();

        return [
            'id' => $response->id,
            'status' => $response->status,
            'due_on' => $response->due_on?->toDateString(),
            'submitted_at' => $response->submitted_at?->toIso8601String(),
            'reviewed_at' => $response->reviewed_at?->toIso8601String(),
            'reviewed_by' => $response->reviewer ? ['id' => $response->reviewer->id, 'name' => $response->reviewer->name] : null,
            'client' => $client ? ['id' => $client->id, 'name' => $client->name, 'avatar_url' => null] : null,
            'form' => $form ? (new CheckinFormResource($form))->resolve(request()) : null,
            'answers' => (object) ($response->answers ?? []),
            'coach_feedback' => $response->coach_feedback,
            'comments' => CheckinResponseResource::comments($response->comments),
            'photos' => $response->photos->map(fn (ProgressPhoto $p) => $this->progress->photoPayload($p, true))->values()->all(),
            'context' => [
                'snapshot' => $response->context ? (object) $response->context : null,
                'metrics' => $metrics->map(fn (BodyMetric $m) => [
                    'measured_on' => $m->measured_on->toDateString(),
                    'weight_kg' => $m->weight_kg,
                    'body_fat_pct' => $m->body_fat_pct,
                    'measurements' => (object) ($m->measurements ?? []),
                ])->values()->all(),
                'compliance' => $client ? array_intersect_key($this->compliance->forClient($client), array_flip(['score', 'risk_status', 'at_risk', 'workouts_pct', 'nutrition_pct', 'habits_pct', 'checkins_pct', 'reasons'])) : null,
                'previous_photos' => $previousPhotos->map(fn (ProgressPhoto $p) => $this->progress->photoPayload($p, true))->values()->all(),
                'previous_responses' => $previous->map(fn (CheckinResponse $r) => [
                    'id' => $r->id,
                    'form_name' => $r->form?->name,
                    'due_on' => $r->due_on?->toDateString(),
                    'submitted_at' => $r->submitted_at?->toIso8601String(),
                    'answers' => (object) ($r->answers ?? []),
                    'coach_feedback' => $r->coach_feedback,
                ])->values()->all(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function schedulePayload(CheckinSchedule $s): array
    {
        return [
            'id' => $s->id,
            'form_id' => $s->checkin_form_id,
            'client_id' => $s->client_id,
            'frequency' => $s->frequency,
            'weekday' => $s->weekday,
            'every_days' => $s->every_days,
            'due_time' => substr((string) $s->due_time, 0, 5),
            'next_due_on' => $s->next_due_on?->toDateString(),
            'is_active' => (bool) $s->is_active,
        ];
    }

    private function authorizeReview(): void
    {
        if (! $this->user()->can('checkins.review')) {
            throw ApiException::forbidden();
        }
    }
}
