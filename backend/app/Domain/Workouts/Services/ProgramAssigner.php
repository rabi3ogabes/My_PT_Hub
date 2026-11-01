<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Assigns programs to clients. Used by the coach API, invitations (program
 * attached to an invite) and the payments fulfilment pipeline. Must run inside
 * the client's tenant context.
 */
class ProgramAssigner
{
    public const UNLOCK_MODES = ['all', 'weekly_drip', 'daily'];

    public function __construct(private readonly ProgramSchedule $schedule) {}

    public function assignById(string $programId, User $client, string $startDate, ?string $assignedBy = null, string $unlockMode = 'all'): ProgramAssignment
    {
        $program = Program::query()->findOrFail($programId);

        return $this->assign($program, $client, $startDate, $assignedBy, $unlockMode);
    }

    /**
     * Starts the program on $startDate (client-local date). Any other active, paused or
     * scheduled assignment of the client is closed as cancelled; re-assigning the same
     * program with the same start date is idempotent.
     */
    public function assign(Program $program, User $client, string $startDate, ?string $assignedBy = null, string $unlockMode = 'all'): ProgramAssignment
    {
        $tenantId = app(TenantContext::class)->id() ?? throw new \LogicException('ProgramAssigner requires a tenant context.');
        if (! in_array($unlockMode, self::UNLOCK_MODES, true)) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['unlock_mode' => [__('workouts.invalid_unlock_mode')]]);
        }
        if ($client->membershipFor($tenantId)?->role !== 'client') {
            throw ApiException::invalid('validation_failed', __('workouts.not_a_client'), ['client_ids' => [__('workouts.not_a_client')]]);
        }
        $start = CarbonImmutable::parse($startDate)->startOfDay();

        return DB::transaction(function () use ($program, $client, $start, $assignedBy, $unlockMode) {
            $open = ProgramAssignment::query()
                ->where('client_id', $client->id)
                ->whereIn('status', ['active', 'paused', 'scheduled'])
                ->lockForUpdate()->get();

            $same = $open->first(fn (ProgramAssignment $a) => $a->program_id === $program->id && $a->start_date->toDateString() === $start->toDateString());
            if ($same) {
                if ($same->unlock_mode !== $unlockMode) {
                    $same->forceFill(['unlock_mode' => $unlockMode])->save();
                }

                return $same->load(['program', 'client']);
            }

            $open->each(fn (ProgramAssignment $a) => $a->forceFill(['status' => 'cancelled'])->save());

            $today = ProgramSchedule::today($client);
            $assignment = ProgramAssignment::create([
                'program_id' => $program->id,
                'client_id' => $client->id,
                'assigned_by' => $assignedBy,
                'start_date' => $start->toDateString(),
                'status' => $start->greaterThan($today) ? 'scheduled' : 'active',
                'unlock_mode' => $unlockMode,
                'shift_days' => 0,
            ]);

            return $assignment->load(['program', 'client']);
        });
    }

    public function pause(ProgramAssignment $assignment, ?CarbonImmutable $on = null): ProgramAssignment
    {
        if ($assignment->status === 'paused') {
            return $assignment;
        }
        if (! in_array($assignment->status, ['active', 'scheduled'], true)) {
            throw ApiException::conflict('assignment_not_active', __('workouts.assignment_not_active'));
        }
        $on ??= ProgramSchedule::today($assignment->loadMissing('client')->client);
        $assignment->forceFill(['status' => 'paused', 'paused_at' => $on->toDateString()])->save();

        return $assignment;
    }

    /** Resumes: the paused days are added to shift_days so the client continues where they stopped. */
    public function resume(ProgramAssignment $assignment, ?CarbonImmutable $on = null): ProgramAssignment
    {
        if ($assignment->status !== 'paused') {
            return $assignment;
        }
        $on ??= ProgramSchedule::today($assignment->loadMissing('client')->client);
        $pausedDays = $assignment->paused_at ? max(0, (int) CarbonImmutable::parse($assignment->paused_at->toDateString())->diffInDays($on, false)) : 0;
        $start = CarbonImmutable::parse($assignment->start_date->toDateString());
        $assignment->forceFill([
            'status' => $start->greaterThan($on) ? 'scheduled' : 'active',
            'paused_at' => null,
            'shift_days' => (int) $assignment->shift_days + $pausedDays,
        ])->save();

        return $assignment;
    }

    /** Moves the schedule by $days (positive = later). shift_days never goes below 0. */
    public function shift(ProgramAssignment $assignment, int $days): ProgramAssignment
    {
        $assignment->forceFill(['shift_days' => max(0, (int) $assignment->shift_days + $days)])->save();

        return $assignment;
    }

    /** Activates scheduled assignments whose start date arrived and completes finished ones. */
    public function refreshStatus(ProgramAssignment $assignment): ProgramAssignment
    {
        if (! in_array($assignment->status, ['scheduled', 'active'], true)) {
            return $assignment;
        }
        $today = ProgramSchedule::today($assignment->loadMissing('client')->client);
        $index = $this->schedule->dayIndex($assignment, $today);
        $weeks = $this->schedule->weeksCount($assignment);
        $status = $index < 0 ? 'scheduled' : ($weeks > 0 && $index >= $weeks * 7 ? 'completed' : 'active');
        if ($status !== $assignment->status) {
            $assignment->forceFill(['status' => $status])->save();
        }

        return $assignment;
    }
}
