<?php

namespace App\Http\Api\V1\Integrations;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Models\HealthDaily;
use App\Domain\Integrations\Models\HealthSample;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\HealthIngestionService;
use App\Domain\Integrations\Services\IntegrationManager;
use App\Domain\Integrations\Services\ReadinessService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Membership;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\HealthSampleResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HealthController extends ApiController
{
    /** Sources a device may report for itself (server-side providers push through webhooks). */
    public const DEVICE_SOURCES = ['apple_health', 'health_connect', 'manual'];

    public function __construct(
        private readonly HealthIngestionService $ingestion,
        private readonly IntegrationManager $manager,
        private readonly ReadinessService $readiness,
    ) {}

    /** Batch upload from Apple Health / Health Connect (background delivery). Idempotent. */
    public function ingest(Request $request): JsonResponse
    {
        $max = (int) config('integrations.health.max_batch', 500);
        $data = $request->validate([
            'samples' => ['required', 'array', 'min:1', 'max:'.$max],
            'samples.*.type' => ['required', 'string', Rule::in(array_keys((array) config('integrations.health.types')))],
            'samples.*.value' => ['required', 'numeric', 'min:0'],
            'samples.*.unit' => ['required', 'string', 'max:12'],
            'samples.*.start_at' => ['required', 'date'],
            'samples.*.end_at' => ['nullable', 'date'],
            'samples.*.source' => ['required', 'string', Rule::in(self::DEVICE_SOURCES)],
            'samples.*.external_id' => ['nullable', 'string', 'max:191'],
            'samples.*.meta' => ['nullable', 'array'],
        ]);

        $user = $this->user();
        $samples = array_map(fn (array $s) => SampleData::make(
            $s['type'], $s['value'], $s['unit'], $s['start_at'], $s['end_at'] ?? null, $s['source'], $s['external_id'] ?? null,
            array_slice((array) ($s['meta'] ?? []), 0, 20, true),
        ), $data['samples']);

        $result = $this->ingestion->ingest($user, $samples);

        foreach (array_unique(array_column($data['samples'], 'source')) as $source) {
            if ($source !== 'manual') {
                $this->manager->recordSuccess($this->manager->connectDevice($user, $source, $this->tenant()));
            }
        }

        return $this->ok($result + ['server_time' => now()->toIso8601String()]);
    }

    public function mySummary(Request $request): JsonResponse
    {
        $data = $request->validate(['days' => ['nullable', 'integer', 'min:1', 'max:90']]);
        $user = $this->user();

        return $this->ok($this->summary($user, (int) ($data['days'] ?? 7)));
    }

    /** Staff: merged timeline (deduplicated samples) + daily aggregates for charts. */
    public function clientTimeline(Request $request, string $client): JsonResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', Rule::in(array_keys((array) config('integrations.health.types')))],
        ]);

        $isClient = Membership::query()->where('user_id', $client)->where('role', 'client')->exists();
        $user = $isClient ? User::query()->find($client) : null;
        if (! $user) {
            throw new ApiException('not_found', __('integrations.errors.client_not_found'), 404);
        }

        $tz = $user->timezone ?: 'UTC';
        $to = isset($data['to']) ? CarbonImmutable::parse($data['to'], $tz)->endOfDay() : CarbonImmutable::now($tz)->endOfDay();
        $from = isset($data['from']) ? CarbonImmutable::parse($data['from'], $tz)->startOfDay() : $to->subDays(13)->startOfDay();
        if ($from->gt($to) || $from->diffInDays($to) > 366) {
            throw ApiException::invalid('validation_failed', __('integrations.errors.invalid_range'), ['from' => [__('integrations.errors.invalid_range')]]);
        }
        $types = $data['types'] ?? null;

        $samples = HealthSample::query()->primary()->where('user_id', $user->id)
            ->when($types, fn ($q) => $q->whereIn('type', $types))
            ->where('start_at', '>=', $from->utc())->where('start_at', '<=', $to->utc())
            ->orderByDesc('start_at')->limit(1000)->get();

        return $this->ok([
            'client' => ['id' => $user->id, 'name' => $user->name, 'timezone' => $tz],
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'timeline' => HealthSampleResource::collection($samples)->resolve($request),
            'daily' => $this->dailySeries($user, $from->toDateString(), $to->toDateString(), $types),
            'readiness' => $this->readiness->score($user),
            'sources' => $this->sources($user),
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(User $user, int $days): array
    {
        $tz = $user->timezone ?: 'UTC';
        $today = CarbonImmutable::now($tz)->toDateString();
        $from = CarbonImmutable::now($tz)->subDays($days - 1)->toDateString();
        $daily = $this->dailySeries($user, $from, $today, null);

        return [
            'date' => $today,
            'today' => (object) collect($daily)->map(fn (array $points) => collect($points)->firstWhere('date', $today)['value'] ?? null)->filter(fn ($v) => $v !== null)->all(),
            'daily' => (object) $daily,
            'readiness' => $this->readiness->score($user),
            'sources' => $this->sources($user),
        ];
    }

    /**
     * @param  list<string>|null  $types
     * @return array<string, list<array{date: string, value: float, source: string}>>
     */
    private function dailySeries(User $user, string $from, string $to, ?array $types): array
    {
        return HealthDaily::query()->where('user_id', $user->id)
            ->when($types, fn ($q) => $q->whereIn('type', $types))
            ->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
            ->orderBy('date')->get()
            ->groupBy('type')
            ->map(fn ($rows) => $rows->map(fn (HealthDaily $d) => ['date' => $d->date->toDateString(), 'value' => (float) $d->value, 'source' => $d->source])->values()->all())
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function sources(User $user): array
    {
        return Integration::query()->where('user_id', $user->id)
            ->whereIn('provider', array_keys(array_filter((array) config('integrations.providers'), fn ($m) => ($m['category'] ?? null) === 'health')))
            ->get()->map(fn (Integration $i) => [
                'provider' => $i->provider,
                'name' => __("integrations.providers.{$i->provider}.name"),
                'status' => $i->status,
                'needs_reconnect' => $i->status === 'error',
                'last_sync_at' => $i->last_sync_at?->toIso8601String(),
            ])->values()->all();
    }
}
