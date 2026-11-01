<?php

namespace App\Domain\Messaging\Listeners;

use App\Domain\Community\Services\BadgeService;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Services\AutomationEngine;
use App\Domain\Tenancy\Events\ClientJoined;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Log;

/** Bridges domain events from other modules to the automation engine and badges. */
final class RunAutomationTriggers
{
    public function __construct(private readonly AutomationEngine $engine, private readonly BadgeService $badges) {}

    public function clientJoined(ClientJoined $event): void
    {
        $this->engine->fire('client_joined', $event->tenant, $event->client);
    }

    /**
     * Handles App\Domain\Workouts\Events\PersonalRecordAchieved. The event is
     * owned by the workouts module, so its shape is read defensively: either
     * `record` (PersonalRecord) or `tenant` + `client`/`user` properties.
     */
    public function personalRecord(object $event): void
    {
        $record = $event->record ?? $event->personalRecord ?? null;
        $tenant = $event->tenant ?? null;
        $client = $event->client ?? $event->user ?? null;

        if (! $tenant instanceof Tenant && $record !== null && isset($record->tenant_id)) {
            $tenant = Tenant::query()->find($record->tenant_id);
        }
        if (! $client instanceof User && $record !== null && isset($record->client_id)) {
            $client = User::query()->find($record->client_id);
        }
        if (! $tenant instanceof Tenant || ! $client instanceof User) {
            Log::warning('automations.pr_event_unresolved', ['event' => $event::class]);

            return;
        }

        $this->engine->fire('pr_hit', $tenant, $client, array_filter([
            'dedupe' => isset($record->id) ? 'pr:'.$record->id : null,
            'record_id' => $record->id ?? null,
        ]));
        $this->badges->evaluate($tenant, $client);
    }

    /** Handles App\Domain\Workouts\Events\WorkoutCompleted: re-evaluates achievement badges right away. */
    public function workoutCompleted(object $event): void
    {
        $session = $event->session ?? null;
        if ($session === null || ! isset($session->tenant_id, $session->client_id)) {
            return;
        }
        $tenant = Tenant::query()->find($session->tenant_id);
        $client = User::query()->find($session->client_id);
        if ($tenant && $client) {
            $this->badges->evaluate($tenant, $client);
        }
    }
}
