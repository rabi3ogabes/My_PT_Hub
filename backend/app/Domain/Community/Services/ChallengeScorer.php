<?php

namespace App\Domain\Community\Services;

use App\Domain\Community\Models\Challenge;
use App\Domain\Community\Models\ChallengeEntry;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\WorkoutSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Computes challenge scores from source data:
 *  steps        → sum of health_daily steps within the challenge dates
 *  workouts     → completed workout sessions
 *  habit_streak → longest run of consecutive days with a completed habit
 *  volume       → total completed session volume (kg)
 *  custom       → entered manually (left untouched)
 * Reaching the goal sets completed_at once and awards the challenge badge.
 */
final class ChallengeScorer
{
    public function __construct(private readonly TenantContext $context, private readonly BadgeService $badges) {}

    public function score(Challenge $challenge): void
    {
        $tenant = Tenant::query()->findOrFail($challenge->tenant_id);
        $this->context->run($tenant, function () use ($challenge, $tenant) {
            $entries = ChallengeEntry::query()->where('challenge_id', $challenge->id)->get();
            $users = User::query()->whereIn('id', $entries->pluck('user_id'))->get()->keyBy('id');
            foreach ($entries as $entry) {
                if ($user = $users->get($entry->user_id)) {
                    $this->scoreEntry($challenge, $entry, $user, $tenant);
                }
            }
        });
    }

    public function scoreEntry(Challenge $challenge, ChallengeEntry $entry, User $user, Tenant $tenant): ChallengeEntry
    {
        if ($challenge->metric !== 'custom') {
            $entry->score = round($this->compute($challenge, $user, $tenant), 2);
        }
        $justCompleted = false;
        if ($entry->completed_at === null && $challenge->goal !== null && $challenge->goal > 0 && $entry->score >= $challenge->goal) {
            $entry->completed_at = now();
            $justCompleted = true;
        }
        $entry->save();

        if ($justCompleted) {
            $this->badges->award($tenant, $user, $challenge->badge_key ?: 'challenge_finisher');
        }

        return $entry;
    }

    /** Scores every challenge that is running (or ended yesterday) across tenants. */
    public function scoreActive(): int
    {
        $today = CarbonImmutable::now()->toDateString();
        $count = 0;
        Challenge::withoutGlobalScopes()->whereDate('starts_on', '<=', $today)
            ->whereDate('ends_on', '>=', CarbonImmutable::now()->subDays(2)->toDateString())
            ->each(function (Challenge $c) use (&$count) {
                $this->score($c);
                $count++;
            });

        return $count;
    }

    private function compute(Challenge $challenge, User $user, Tenant $tenant): float
    {
        $tz = $user->timezone && $user->timezone !== 'UTC' ? $user->timezone : ($tenant->timezone ?: 'UTC');
        $startDate = $challenge->starts_on->toDateString();
        $endDate = $challenge->ends_on->toDateString();
        $from = CarbonImmutable::parse($startDate, $tz)->startOfDay()->utc();
        $to = CarbonImmutable::parse($endDate, $tz)->endOfDay()->utc();

        return match ($challenge->metric) {
            'steps' => (float) DB::table('health_daily')->where('user_id', $user->id)->where('type', 'steps')
                ->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->sum('value'),
            'workouts' => (float) WorkoutSession::query()->where('client_id', $user->id)->where('status', 'completed')
                ->whereBetween('started_at', [$from, $to])->count(),
            'volume' => (float) WorkoutSession::query()->where('client_id', $user->id)->where('status', 'completed')
                ->whereBetween('started_at', [$from, $to])->sum('total_volume_kg'),
            'habit_streak' => (float) $this->longestHabitStreak($user, $startDate, $endDate),
            default => 0.0,
        };
    }

    private function longestHabitStreak(User $user, string $from, string $to): int
    {
        $assignmentIds = HabitAssignment::query()->where('client_id', $user->id)->pluck('id');
        if ($assignmentIds->isEmpty()) {
            return 0;
        }
        $dates = HabitLog::query()->whereIn('habit_assignment_id', $assignmentIds)->where('completed', true)
            ->whereDate('log_date', '>=', $from)->whereDate('log_date', '<=', $to)
            ->pluck('log_date')->map(fn ($d) => CarbonImmutable::parse($d)->toDateString())->unique()->sort()->values();

        $best = 0;
        $run = 0;
        $previous = null;
        foreach ($dates as $date) {
            $run = $previous !== null && CarbonImmutable::parse($previous)->addDay()->toDateString() === $date ? $run + 1 : 1;
            $best = max($best, $run);
            $previous = $date;
        }

        return $best;
    }
}
