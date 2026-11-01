<?php

namespace App\Http\Api\V1\Sync;

use App\Domain\Workouts\Services\Sync\SyncPullService;
use App\Domain\Workouts\Services\Sync\SyncPushService;
use App\Http\Api\ApiController;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Offline sync for the client apps (own data only). */
class SyncController extends ApiController
{
    public function __construct(
        private readonly SyncPullService $puller,
        private readonly SyncPushService $pusher,
    ) {}

    /** GET /sync/pull?since=<ISO 8601> */
    public function pull(Request $request): JsonResponse
    {
        $data = $request->validate(['since' => ['nullable', 'date']]);
        $since = isset($data['since']) ? CarbonImmutable::parse($data['since'])->utc() : null;

        return $this->ok($this->puller->pull($this->user(), $since));
    }

    /** POST /sync/push {mutations: [{id, entity, op, payload, client_time}]} */
    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mutations' => ['required', 'array', 'min:1', 'max:500'],
            'mutations.*.id' => ['required', 'string', 'ulid', 'distinct'],
            'mutations.*.entity' => ['required', Rule::in(SyncPushService::ENTITIES)],
            'mutations.*.op' => ['required', Rule::in(['upsert', 'delete'])],
            'mutations.*.payload' => ['present', 'array'],
            'mutations.*.client_time' => ['nullable', 'date'],
        ]);
        // Keep the full payload objects (validated() would drop unlisted payload keys).
        $mutations = collect($request->input('mutations'))->map(fn ($m, $i) => [
            ...$data['mutations'][$i],
            'payload' => (array) ($m['payload'] ?? []),
        ])->values()->all();

        return $this->ok([
            'results' => $this->pusher->push($this->user(), $mutations),
            'server_time' => now()->utc()->toIso8601String(),
        ]);
    }
}
