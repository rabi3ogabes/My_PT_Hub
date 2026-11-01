<?php

namespace App\Domain\Progress\Services;

use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Habits\Services\StreakService;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Services\TargetService;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Notifications\GenericNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Per-client compliance score (0–100) over a rolling window:
 *  - workouts: completed sessions vs planned program days,
 *  - nutrition: days whose logged kcal is within ±10% of target,
 *  - habits: completed vs scheduled habit occurrences,
 *  - check-ins: submitted vs due check-ins (28-day window).
 * Components without data are excluded and weights renormalised. Weights come
 * from tenant settings `compliance_weights`. Status: green ≥ 75, amber ≥ 50,
 * red otherwise; at-risk (forced red) = 3+ days inactive or a score drop > 25 %
 * versus the previous window.
 */
final class ComplianceService
{
    public const DEFAULT_WEIGHTS = ['workouts' => 40, 'nutrition' => 25, 'habits' => 20, 'checkins' => 15];

    public const GREEN = 75;

    public const AMBER = 50;

    public const INACTIVE_DAYS = 3;

    public const DROP_RATIO = 0.25;

    public function __construct(
        private readonly TrainingSchedule $schedule,
        private readonly TargetService $targets,
        private readonly StreakService $streaks,
        private readonly TenantContext $context,
    ) {}

    /** @return array{workouts: float, nutrition: float, habits: float, checkins: float} */
    public function weights(?Tenant $tenant = null): array
    {
        $tenant ??= $this->context->get();
        $configured = (array) ($tenant?->setting('compliance_weights') ?? []);
        $weights = [];
        foreach (self::DEFAULT_WEIGHTS as $key => $default) {
            $weights[$key] = max(0.0, (float) ($configured[$key] ?? $default));
        }

        return $weights;
    }

    /**
     * Compliance of a client in the current tenant.
     *
     * @return array{score: int|null, risk_status: string, at_risk: bool, workouts_pct: int|null, nutrition_pct: int|null, habits_pct: int|null, checkins_pct: int|null, previous_score: int|null, days_inactive: int|null, last_active_on: string|null, weights: array<string, float>, components: array<string, mixed>, reasons: list<string>}
     */
    public function forClient(User $client, ?string $date = null, int $days = 7): array
    {
        $today = $date ?? CarbonImmutable::now($client->timezone ?: 'UTC')->toDateString();
        $end = CarbonImmutable::parse($today);
        $current = $this->window($client, $end->subDays($days - 1)->toDateString(), $end->toDateString(), $today);
        $previous = $this->window($client, $end->subDays(2 * $days - 1)->toDateString(), $end->subDays($days)->toDateString(), $today);

        $weights = $this->weights();
        $score = $this->score($current, $weights);
        $previousScore = $this->score($previous, $weights);

        $lastActive = $this->lastActiveOn($client, $today);
        $daysInactive = $lastActive ? (int) CarbonImmutable::parse($lastActive)->diffInDays($end) : null;

        $reasons = [];
        if ($daysInactive !== null && $daysInactive >= self::INACTIVE_DAYS) {
            $reasons[] = 'inactive';
        }
        if ($score !== null && $previousScore !== null && $previousScore > 0 && ($previousScore - $score) / $previousScore > self::DROP_RATIO) {
            $reasons[] = 'score_drop';
        }
        foreach (['workouts', 'nutrition', 'habits', 'checkins'] as $key) {
            if ($current[$key]['pct'] !== null && $current[$key]['pct'] < self::AMBER) {
                $reasons[] = 'low_'.$key;
            }
        }
        $atRisk = in_array('inactive', $reasons, true) || in_array('score_drop', $reasons, true);
        $status = match (true) {
            $atRisk => 'red',
            $score === null => 'green',
            $score >= self::GREEN => 'green',
            $score >= self::AMBER => 'amber',
            default => 'red',
        };

        return [
            'score' => $score,
            'risk_status' => $status,
            'at_risk' => $atRisk,
            'workouts_pct' => $current['workouts']['pct'],
            'nutrition_pct' => $current['nutrition']['pct'],
            'habits_pct' => $current['habits']['pct'],
            'checkins_pct' => $current['checkins']['pct'],
            'previous_score' => $previousScore,
            'days_inactive' => $daysInactive,
            'last_active_on' => $lastActive,
            'weights' => $weights,
            'components' => $current,
            'reasons' => $reasons,
        ];
    }

    /** Computes and stores client_profiles.compliance_score / risk_status. */
    public function refresh(User $client, ?string $date = null): array
    {
        $result = $this->forClient($client, $date);
        ClientProfile::query()->where('user_id', $client->id)->update([
            'compliance_score' => $result['score'],
            'risk_status' => $result['risk_status'],
        ]);

        return $result;
    }

    /**
     * Refreshes every active client of a tenant; returns results keyed by client id.
     *
     * @return array<string, array<string, mixed>>
     */
    public function refreshTenant(Tenant $tenant): array
    {
        return $this->context->run($tenant, function () {
            $ids = Membership::query()->where('role', 'client')->where('status', 'active')->pluck('user_id');
            $out = [];
            foreach (User::query()->whereIn('id', $ids)->get() as $client) {
                $out[$client->id] = $this->refresh($client);
            }

            return $out;
        });
    }

    /**
     * Daily digest: refreshes scores and notifies each coach (or the owners) about at-risk clients.
     * Sent when the tenant's local time is $hour (the schedule runs hourly).
     *
     * @return int notifications sent
     */
    public function sendDigests(?int $hour = 7, ?Tenant $only = null): int
    {
        $sent = 0;
        $tenants = $only ? [$only] : Tenant::query()->where('type', 'business')->cursor();
        foreach ($tenants as $tenant) {
            if ($hour !== null && (int) CarbonImmutable::now($tenant->timezone ?: 'UTC')->format('G') !== $hour) {
                continue;
            }
            $results = $this->refreshTenant($tenant);
            $sent += $this->context->run($tenant, function () use ($results) {
                $atRisk = collect($results)->filter(fn ($r) => $r['at_risk'] || $r['risk_status'] === 'red');
                if ($atRisk->isEmpty()) {
                    return 0;
                }
                $profiles = ClientProfile::query()->whereIn('user_id', $atRisk->keys())->get()->keyBy('user_id');
                $owners = Membership::query()->where('role', 'owner')->where('status', 'active')->pluck('user_id')->all();
                $byCoach = [];
                foreach ($atRisk->keys() as $clientId) {
                    $coachId = $profiles[$clientId]->coach_id ?? null;
                    foreach ($coachId ? [$coachId] : $owners as $recipient) {
                        $byCoach[$recipient][] = $clientId;
                    }
                }
                $users = User::query()->whereIn('id', array_merge(array_keys($byCoach), $atRisk->keys()->all()))->get()->keyBy('id');
                $count = 0;
                foreach ($byCoach as $coachId => $clientIds) {
                    $coach = $users[$coachId] ?? null;
                    if (! $coach) {
                        continue;
                    }
                    $locale = $coach->preferredLocale();
                    $names = collect($clientIds)->map(fn ($id) => $users[$id]->name ?? null)->filter()->take(5)->implode(', ');
                    $coach->notify(new GenericNotification(
                        'checkins',
                        __('progress.digest.title', ['count' => count($clientIds)], $locale),
                        __('progress.digest.body', ['count' => count($clientIds), 'names' => $names], $locale),
                        '/compliance?at_risk=1',
                        ['client_ids' => implode(',', $clientIds)],
                        __('progress.digest.action', [], $locale),
                    ));
                    $count++;
                }

                return $count;
            });
        }

        return $sent;
    }

    /**
     * @param  array<string, array{pct: int|null}>  $window
     * @param  array<string, float>  $weights
     */
    private function score(array $window, array $weights): ?int
    {
        $total = 0.0;
        $weightSum = 0.0;
        foreach ($weights as $key => $weight) {
            $pct = $window[$key]['pct'] ?? null;
            if ($pct === null || $weight <= 0) {
                continue;
            }
            $total += $pct * $weight;
            $weightSum += $weight;
        }

        return $weightSum > 0 ? (int) round($total / $weightSum) : null;
    }

    /** @return array<string, array<string, mixed>> */
    private function window(User $client, string $from, string $to, string $today): array
    {
        return [
            'workouts' => $this->workouts($client, $from, $to, $today),
            'nutrition' => $this->nutrition($client, $from, $to, $today),
            'habits' => $this->habits($client, $from, $to, $today),
            'checkins' => $this->checkins($client, CarbonImmutable::parse($to)->subDays(27)->toDateString(), $to, $today),
        ];
    }

    /** @return array{pct: int|null, planned: int, completed: int} */
    private function workouts(User $client, string $from, string $to, string $today): array
    {
        $planned = $this->schedule->plannedWorkouts($client->id, $from, $to);
        // Today's planned workout only counts once done (the day isn't over).
        $completedSessions = WorkoutSession::query()->where('client_id', $client->id)->where('status', 'completed')
            ->whereBetween('started_at', [CarbonImmutable::parse($from, $client->timezone ?: 'UTC')->startOfDay()->utc(), CarbonImmutable::parse($to, $client->timezone ?: 'UTC')->endOfDay()->utc()])
            ->get(['id', 'program_day_id', 'started_at']);
        $doneToday = $completedSessions->contains(fn ($s) => $s->started_at->copy()->setTimezone($client->timezone ?: 'UTC')->toDateString() === $today);
        $plannedCount = 0;
        foreach ($planned as $date => $dayIds) {
            if ($date === $today && ! $doneToday) {
                continue;
            }
            $plannedCount += count($dayIds);
        }
        $completed = $completedSessions->count();
        if ($plannedCount === 0) {
            return ['pct' => null, 'planned' => 0, 'completed' => $completed];
        }

        return ['pct' => (int) round(min($completed, $plannedCount) / $plannedCount * 100), 'planned' => $plannedCount, 'completed' => $completed];
    }

    /** @return array{pct: int|null, days_with_target: int, days_on_target: int, days_logged: int} */
    private function nutrition(User $client, string $from, string $to, string $today): array
    {
        $byDay = FoodLog::query()->where('client_id', $client->id)
            ->whereDate('log_date', '>=', $from)->whereDate('log_date', '<=', $to)
            ->get(['log_date', 'kcal'])->groupBy(fn ($l) => $l->log_date->toDateString())
            ->map(fn (Collection $logs) => (float) $logs->sum('kcal'));

        $withTarget = 0;
        $onTarget = 0;
        for ($d = CarbonImmutable::parse($from); $d->toDateString() <= $to; $d = $d->addDay()) {
            $key = $d->toDateString();
            if ($key === $today && ! isset($byDay[$key])) {
                continue;
            }
            $target = $this->targets->forDate($client->id, $key);
            if (! $target || (int) $target->kcal <= 0) {
                continue;
            }
            $withTarget++;
            if (isset($byDay[$key]) && abs($byDay[$key] - $target->kcal) <= $target->kcal * 0.10) {
                $onTarget++;
            }
        }

        return [
            'pct' => $withTarget ? (int) round($onTarget / $withTarget * 100) : null,
            'days_with_target' => $withTarget,
            'days_on_target' => $onTarget,
            'days_logged' => $byDay->count(),
        ];
    }

    /** @return array{pct: int|null, scheduled: int, completed: int} */
    private function habits(User $client, string $from, string $to, string $today): array
    {
        $assignments = HabitAssignment::query()->where('client_id', $client->id)
            ->whereDate('start_date', '<=', $to)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $from))
            ->with(['logs' => fn ($q) => $q->whereDate('log_date', '>=', $from)->whereDate('log_date', '<=', $to)])
            ->get();
        $scheduled = 0;
        $completed = 0;
        foreach ($assignments as $assignment) {
            $done = $assignment->logs->where('completed', true)->map(fn (HabitLog $l) => $l->log_date->toDateString())->unique()->flip();
            if ($assignment->schedule === 'per_week') {
                $windowDays = 0;
                $count = 0;
                for ($d = CarbonImmutable::parse($from); $d->toDateString() <= $to; $d = $d->addDay()) {
                    $key = $d->toDateString();
                    if ($key < $assignment->start_date->toDateString() || ($key === $today && ! isset($done[$key]))) {
                        continue;
                    }
                    $windowDays++;
                    $count += isset($done[$key]) ? 1 : 0;
                }
                $expected = (int) round(((int) ($assignment->per_week ?: 1)) * $windowDays / 7);
                $scheduled += $expected;
                $completed += min($count, $expected);

                continue;
            }
            for ($d = CarbonImmutable::parse($from); $d->toDateString() <= $to; $d = $d->addDay()) {
                $key = $d->toDateString();
                if (! $this->streaks->isScheduled($assignment, $key) || ($key === $today && ! isset($done[$key]))) {
                    continue;
                }
                $scheduled++;
                $completed += isset($done[$key]) ? 1 : 0;
            }
        }

        return ['pct' => $scheduled ? (int) round($completed / $scheduled * 100) : null, 'scheduled' => $scheduled, 'completed' => $completed];
    }

    /** @return array{pct: int|null, due: int, submitted: int} */
    private function checkins(User $client, string $from, string $to, string $today): array
    {
        $responses = CheckinResponse::query()->where('client_id', $client->id)
            ->whereDate('due_on', '>=', $from)->whereDate('due_on', '<=', $to)->get(['status', 'due_on']);
        // Pending check-ins that are not yet late don't count against the client.
        $due = $responses->filter(fn ($r) => ! ($r->status === 'pending' && $r->due_on->toDateString() >= $today));
        $submitted = $due->whereIn('status', ['submitted', 'reviewed'])->count();

        return ['pct' => $due->count() ? (int) round($submitted / $due->count() * 100) : null, 'due' => $due->count(), 'submitted' => $submitted];
    }

    /** Latest local date with any client activity (logs, sessions, habits, check-ins, metrics), or the join date. */
    public function lastActiveOn(User $client, string $today): ?string
    {
        $tz = $client->timezone ?: 'UTC';
        $candidates = [
            FoodLog::query()->where('client_id', $client->id)->whereDate('log_date', '<=', $today)->max('log_date'),
            BodyMetric::query()->where('client_id', $client->id)->whereDate('measured_on', '<=', $today)->max('measured_on'),
            HabitLog::query()->whereIn('habit_assignment_id', HabitAssignment::query()->where('client_id', $client->id)->select('id'))
                ->where('completed', true)->whereDate('log_date', '<=', $today)->max('log_date'),
        ];
        $session = WorkoutSession::query()->where('client_id', $client->id)->max('started_at');
        $checkin = CheckinResponse::query()->where('client_id', $client->id)->max('submitted_at');
        $joined = Membership::query()->where('user_id', $client->id)->value('joined_at');
        foreach ([$session, $checkin, $joined] as $ts) {
            if ($ts) {
                $candidates[] = CarbonImmutable::parse($ts, 'UTC')->setTimezone($tz)->toDateString();
            }
        }
        $dates = array_values(array_filter(array_map(fn ($d) => $d ? substr((string) $d, 0, 10) : null, $candidates)));
        $dates = array_filter($dates, fn ($d) => $d <= $today);

        return $dates ? max($dates) : null;
    }
}
