<?php

namespace App\Domain\Community\Services;

use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Community\Models\UserBadge;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Services\Notifier;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\WorkoutSession;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Achievements: first workout, 7/30/100-day activity streaks, 10 personal
 * records, 4 consecutive check-ins, challenge completion. Awards are unique
 * per (tenant, user, badge) so evaluation is idempotent.
 */
final class BadgeService
{
    public const BADGES = ['first_workout', 'streak_7', 'streak_30', 'streak_100', 'pr_10', 'checkin_streak_4', 'challenge_finisher'];

    private const STREAKS = [7 => 'streak_7', 30 => 'streak_30', 100 => 'streak_100'];

    public function __construct(private readonly TenantContext $context, private readonly Notifier $notifier) {}

    /**
     * Evaluates every rule for a user and awards what was earned.
     *
     * @return list<string> newly awarded badge keys
     */
    public function evaluate(Tenant $tenant, User $user): array
    {
        return $this->context->run($tenant, function () use ($tenant, $user) {
            $earned = [];
            $completed = WorkoutSession::query()->where('client_id', $user->id)->where('status', 'completed');
            if ($completed->exists()) {
                $earned[] = 'first_workout';
            }
            $streak = $this->activityStreak($user, $tenant);
            foreach (self::STREAKS as $days => $key) {
                if ($streak >= $days) {
                    $earned[] = $key;
                }
            }
            if (PersonalRecord::query()->where('client_id', $user->id)->count() >= 10) {
                $earned[] = 'pr_10';
            }
            if ($this->checkinStreak($user, $tenant) >= 4) {
                $earned[] = 'checkin_streak_4';
            }

            return array_values(array_filter($earned, fn (string $key) => $this->award($tenant, $user, $key)));
        });
    }

    /** Awards a badge once; returns true when newly awarded. */
    public function award(Tenant $tenant, User $user, string $key): bool
    {
        $created = $this->context->run($tenant, function () use ($tenant, $user, $key) {
            if (UserBadge::query()->where('user_id', $user->id)->where('badge_key', $key)->exists()) {
                return false;
            }
            try {
                DB::transaction(fn () => UserBadge::create(['tenant_id' => $tenant->id, 'user_id' => $user->id, 'badge_key' => $key, 'awarded_at' => now()]));

                return true;
            } catch (UniqueConstraintViolationException) {
                return false;
            }
        });

        if ($created) {
            $this->notifier->send($user, 'community', 'community_module.badges.awarded.title', 'community_module.badges.awarded.body', [
                'badge' => $this->notifier->inLocale($user, fn () => $this->label($key)),
            ], '/badges', ['badge_key' => $key]);
        }

        return $created;
    }

    public function label(string $key): string
    {
        $translationKey = 'community_module.badges.names.'.$key;
        $label = __($translationKey);

        return $label === $translationKey ? (string) __('community_module.badges.names.challenge_finisher') : (string) $label;
    }

    public function description(string $key): string
    {
        $translationKey = 'community_module.badges.descriptions.'.$key;
        $label = __($translationKey);

        return $label === $translationKey ? (string) __('community_module.badges.descriptions.challenge_finisher') : (string) $label;
    }

    /** Consecutive days (ending today or yesterday, user-local) with a completed workout or habit. */
    public function activityStreak(User $user, Tenant $tenant, ?CarbonImmutable $now = null): int
    {
        $tz = $user->timezone && $user->timezone !== 'UTC' ? $user->timezone : ($tenant->timezone ?: 'UTC');
        $today = ($now ?? CarbonImmutable::now())->setTimezone($tz)->startOfDay();
        $since = $today->subDays(120);

        $days = [];
        WorkoutSession::query()->where('client_id', $user->id)->where('status', 'completed')
            ->where('started_at', '>=', $since->utc())->pluck('started_at')
            ->each(function ($at) use (&$days, $tz) {
                $days[CarbonImmutable::parse($at)->setTimezone($tz)->toDateString()] = true;
            });
        $assignmentIds = HabitAssignment::query()->where('client_id', $user->id)->pluck('id');
        if ($assignmentIds->isNotEmpty()) {
            HabitLog::query()->whereIn('habit_assignment_id', $assignmentIds)->where('completed', true)
                ->whereDate('log_date', '>=', $since->toDateString())->pluck('log_date')
                ->each(function ($d) use (&$days) {
                    $days[CarbonImmutable::parse($d)->toDateString()] = true;
                });
        }

        $cursor = isset($days[$today->toDateString()]) ? $today : $today->subDay();
        $streak = 0;
        while (isset($days[$cursor->toDateString()])) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /** Consecutive most recent due check-ins that were submitted. */
    public function checkinStreak(User $user, Tenant $tenant): int
    {
        $today = CarbonImmutable::now($tenant->timezone ?: 'UTC')->toDateString();
        $streak = 0;
        foreach (CheckinResponse::query()->where('client_id', $user->id)->whereNotNull('due_on')->whereDate('due_on', '<=', $today)->orderByDesc('due_on')->limit(52)->get(['status']) as $response) {
            if (! in_array($response->status, ['submitted', 'reviewed'], true)) {
                break;
            }
            $streak++;
        }

        return $streak;
    }

    /** Daily sweep over every active client of every tenant. */
    public function sweep(): int
    {
        $awarded = 0;
        Membership::withoutGlobalScopes()->where('role', 'client')->where('status', 'active')
            ->orderBy('tenant_id')->chunk(500, function ($memberships) use (&$awarded) {
                $tenants = Tenant::query()->whereIn('id', $memberships->pluck('tenant_id')->unique())->get()->keyBy('id');
                $users = User::query()->whereIn('id', $memberships->pluck('user_id')->unique())->get()->keyBy('id');
                foreach ($memberships as $m) {
                    if (($tenant = $tenants->get($m->tenant_id)) && ($user = $users->get($m->user_id))) {
                        $awarded += count($this->evaluate($tenant, $user));
                    }
                }
            });

        return $awarded;
    }
}
