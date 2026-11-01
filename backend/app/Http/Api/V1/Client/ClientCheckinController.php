<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Checkins\Services\CheckinService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Checkins\CheckinFormResource;
use App\Http\Api\V1\Resources\Checkins\CheckinResponseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientCheckinController extends ApiController
{
    public function __construct(private readonly CheckinService $checkins) {}

    /** GET /me/checkins */
    public function index(): JsonResponse
    {
        $responses = CheckinResponse::query()->where('client_id', $this->user()->id)
            ->with(['form' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('due_on')->orderByDesc('created_at')->limit(100)->get();

        return $this->ok(CheckinResponseResource::collection($responses)->resolve());
    }

    /** GET /me/checkins/{response} */
    public function show(CheckinResponse $response): JsonResponse
    {
        $this->own($response);

        return $this->ok($this->detail($response));
    }

    /** POST /me/checkins/{response}/submit */
    public function submit(Request $request, CheckinResponse $response): JsonResponse
    {
        $this->own($response);
        $data = $request->validate([
            'answers' => ['present', 'array'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:400'],
            'body_fat_pct' => ['nullable', 'numeric', 'min:2', 'max:70'],
            'measurements' => ['nullable', 'array'],
            'measurements.*' => ['numeric', 'min:5', 'max:300'],
            'photo_ids' => ['nullable', 'array', 'max:12'],
            'photo_ids.*' => ['string', 'size:26'],
        ]);
        $response = $this->checkins->submit($response, $this->user(), $data);

        return $this->ok($this->detail($response));
    }

    private function own(CheckinResponse $response): void
    {
        if ($response->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
    }

    /** @return array<string, mixed> */
    private function detail(CheckinResponse $response): array
    {
        $response->load(['form' => fn ($q) => $q->withTrashed(), 'comments.user']);
        $form = $response->form;

        return [
            'id' => $response->id,
            'status' => $response->status,
            'due_on' => $response->due_on?->toDateString(),
            'form' => $form ? [
                'id' => $form->id,
                'name' => $form->name,
                'intro' => $form->intro,
                'questions' => CheckinFormResource::questions($form->questions ?? []),
                'include_metrics' => (bool) $form->include_metrics,
                'include_photos' => (bool) $form->include_photos,
            ] : null,
            'answers' => (object) ($response->answers ?? []),
            'coach_feedback' => $response->coach_feedback,
            'comments' => CheckinResponseResource::comments($response->comments),
        ];
    }
}
