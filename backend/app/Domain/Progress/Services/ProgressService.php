<?php

namespace App\Domain\Progress\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Workouts\Models\PersonalRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** Progress photos on the private disk (signed, short-lived URLs), body metrics and the progress summary. */
final class ProgressService
{
    public const DISK = 'private';

    public const URL_TTL_MINUTES = 10;

    public function __construct(private readonly ComplianceService $compliance) {}

    public function storePhoto(User $client, UploadedFile $file, string $pose, string $takenOn, ?string $responseId = null): ProgressPhoto
    {
        $id = (string) Str::ulid();
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $tenantId = app(\App\Domain\Shared\Tenancy\TenantContext::class)->id();
        $path = Storage::disk(self::DISK)->putFileAs("tenants/{$tenantId}/progress/{$client->id}", $file, "{$id}.{$ext}");

        return ProgressPhoto::create([
            'id' => $id,
            'client_id' => $client->id,
            'checkin_response_id' => $responseId,
            'taken_on' => $takenOn,
            'pose' => $pose,
            'disk' => self::DISK,
            'path' => $path,
            'is_private' => true,
        ]);
    }

    /** Signed URL for the client's own photo route. */
    public function clientUrl(ProgressPhoto $photo): string
    {
        return URL::temporarySignedRoute('api.v1.progress-photos.show', now()->addMinutes(self::URL_TTL_MINUTES), ['photo' => $photo->id]);
    }

    /** Signed URL for staff (coach) access. */
    public function staffUrl(ProgressPhoto $photo): string
    {
        return URL::temporarySignedRoute('api.v1.clients.progress-photos.show', now()->addMinutes(self::URL_TTL_MINUTES), ['client' => $photo->client_id, 'photo' => $photo->id]);
    }

    /** @return array{id: string, pose: string, taken_on: string, url: string} */
    public function photoPayload(ProgressPhoto $photo, bool $staff = false): array
    {
        return [
            'id' => $photo->id,
            'pose' => $photo->pose,
            'taken_on' => $photo->taken_on->toDateString(),
            'url' => $staff ? $this->staffUrl($photo) : $this->clientUrl($photo),
        ];
    }

    /**
     * @param  array<string, mixed>  $data  measured_on, weight_kg?, body_fat_pct?, measurements?
     */
    public function recordMetric(User $client, array $data, string $source = 'manual'): BodyMetric
    {
        $metric = BodyMetric::query()->where('client_id', $client->id)->whereDate('measured_on', $data['measured_on'])->where('source', $source)->first()
            ?? new BodyMetric(['client_id' => $client->id, 'measured_on' => $data['measured_on'], 'source' => $source]);
        $attributes = [];
        foreach (['weight_kg', 'body_fat_pct'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $attributes[$key] = (float) $data[$key];
            }
        }
        if (! empty($data['measurements'])) {
            $attributes['measurements'] = array_merge($metric->measurements ?? [], array_map('floatval', $data['measurements']));
        }
        $metric->fill($attributes)->save();

        return $metric;
    }

    /**
     * GET /me/progress (and the coach view of a client).
     *
     * @return array<string, mixed>
     */
    public function summary(User $client, bool $staff = false): array
    {
        $metrics = BodyMetric::query()->where('client_id', $client->id)->orderBy('measured_on')->get();
        $byDate = fn (string $field) => $metrics->whereNotNull($field)->groupBy(fn ($m) => $m->measured_on->toDateString())
            ->map(fn (Collection $rows) => (float) $rows->last()->{$field});

        $weight = $byDate('weight_kg')->map(fn ($v, $d) => ['date' => $d, 'weight_kg' => $v])->values()->all();
        $bodyFat = $byDate('body_fat_pct')->map(fn ($v, $d) => ['date' => $d, 'value' => $v])->values()->all();
        $measurements = $metrics->filter(fn ($m) => ! empty($m->measurements))
            ->groupBy(fn ($m) => $m->measured_on->toDateString())
            ->map(fn (Collection $rows, $d) => ['date' => $d, 'values' => (object) $rows->reduce(fn ($carry, $m) => array_merge($carry, $m->measurements), [])])
            ->values()->all();

        $photos = ProgressPhoto::query()->where('client_id', $client->id)->orderByDesc('taken_on')->orderByDesc('created_at')->limit(60)->get()
            ->map(fn (ProgressPhoto $p) => $this->photoPayload($p, $staff))->all();

        $records = PersonalRecord::query()->where('client_id', $client->id)->with('exercise')->orderByDesc('achieved_at')->get()
            ->groupBy(fn ($r) => $r->exercise_id.'|'.$r->type)
            ->map(fn (Collection $rows) => $rows->sortByDesc('value')->first())
            ->sortByDesc('achieved_at')->values()
            ->map(fn (PersonalRecord $r) => [
                'id' => $r->id,
                'exercise_id' => $r->exercise_id,
                'exercise_name' => $r->exercise?->translate('name'),
                'type' => $r->type,
                'value' => (float) $r->value,
                'previous_value' => $r->previous_value !== null ? (float) $r->previous_value : null,
                'achieved_at' => $r->achieved_at?->toIso8601String(),
            ])->all();

        $c = $this->compliance->forClient($client);

        return [
            'weight' => $weight,
            'body_fat' => $bodyFat,
            'measurements' => $measurements,
            'photos' => $photos,
            'records' => $records,
            'compliance' => [
                'score' => $c['score'],
                'workouts_pct' => $c['workouts_pct'],
                'nutrition_pct' => $c['nutrition_pct'],
                'habits_pct' => $c['habits_pct'],
                'checkins_pct' => $c['checkins_pct'],
            ],
        ];
    }
}
