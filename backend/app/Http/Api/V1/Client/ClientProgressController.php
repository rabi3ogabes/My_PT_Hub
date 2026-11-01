<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Checkins\Services\CheckinService;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Progress\Services\ProgressService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ClientProgressController extends ApiController
{
    public function __construct(private readonly ProgressService $progress) {}

    /** POST /me/progress-photos (multipart: photo, pose, taken_on, checkin_response_id?) */
    public function photo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:15360'],
            'pose' => ['required', Rule::in(['front', 'side', 'back'])],
            'taken_on' => ['required', 'date_format:Y-m-d'],
            'checkin_response_id' => ['nullable', 'string', 'size:26'],
        ]);
        if (! empty($data['checkin_response_id'])) {
            $owns = CheckinResponse::query()->whereKey($data['checkin_response_id'])->where('client_id', $this->user()->id)->exists();
            if (! $owns) {
                throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['checkin_response_id' => [__('api.not_found')]]);
            }
        }
        $photo = $this->progress->storePhoto($this->user(), $request->file('photo'), $data['pose'], $data['taken_on'], $data['checkin_response_id'] ?? null);

        return $this->created($this->progress->photoPayload($photo));
    }

    /** GET /me/progress-photos/{photo} — requires a valid temporary signature and ownership. */
    public function showPhoto(Request $request, ProgressPhoto $photo): Response
    {
        if (! $request->hasValidSignature()) {
            throw ApiException::forbidden(__('progress.errors.invalid_signature'));
        }
        if ($photo->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return $this->stream($photo);
    }

    /** POST /me/body-metrics {measured_on, weight_kg?, body_fat_pct?, measurements?} */
    public function metric(Request $request): JsonResponse
    {
        $data = $request->validate([
            'measured_on' => ['required', 'date_format:Y-m-d'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:400', 'required_without_all:body_fat_pct,measurements'],
            'body_fat_pct' => ['nullable', 'numeric', 'min:2', 'max:70'],
            'measurements' => ['nullable', 'array'],
            'measurements.*' => ['numeric', 'min:5', 'max:300'],
        ]);
        if (! empty($data['measurements']) && array_diff(array_keys($data['measurements']), CheckinService::MEASUREMENTS)) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['measurements' => [__('progress.errors.unknown_measurement')]]);
        }
        $metric = $this->progress->recordMetric($this->user(), $data);

        return $this->created([
            'id' => $metric->id,
            'measured_on' => $metric->measured_on->toDateString(),
            'weight_kg' => $metric->weight_kg,
            'body_fat_pct' => $metric->body_fat_pct,
            'measurements' => (object) ($metric->measurements ?? []),
        ]);
    }

    /** GET /me/progress */
    public function summary(): JsonResponse
    {
        return $this->ok($this->progress->summary($this->user()));
    }

    private function stream(ProgressPhoto $photo): Response
    {
        $disk = Storage::disk($photo->disk ?: ProgressService::DISK);
        if (! $disk->exists($photo->path)) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return $disk->response($photo->path, null, ['Cache-Control' => 'private, max-age=600']);
    }
}
