<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Automation;
use App\Domain\Messaging\Models\AutomationRun;
use App\Domain\Messaging\Models\MessageTemplate;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\WorkoutSession;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trigger → conditions → actions automations.
 *
 * Fire-once is guaranteed by automation_runs.dedupe_key (unique): a run row
 * is inserted before any action executes, so concurrent or repeated triggers
 * for the same automation/client/occurrence never act twice.
 *
 * Other modules call fire() directly, e.g.
 *   app(AutomationEngine::class)->fire('payment_failed', $tenant, $client, ['dedupe' => $payment->id]);
 */
final class AutomationEngine
{
    public const TRIGGERS = ['client_joined', 'program_started', 'program_finished', 'workout_missed', 'checkin_overdue', 'pr_hit', 'birthday', 'package_expiring', 'payment_failed'];

    public const ACTIONS = ['send_message', 'assign_habit', 'assign_program', 'notify_coach', 'add_tag'];

    public const CONDITION_FIELDS = ['goal', 'level', 'gender', 'status', 'risk_status', 'compliance_score', 'tags', 'coach_id', 'locale', 'days_since_joined', 'age'];

    public const CONDITION_OPS = ['eq', 'neq', 'in', 'not_in', 'contains', 'not_contains', 'gt', 'gte', 'lt', 'lte'];

    private const PROGRAM_ASSIGNER = 'App\\Domain\\Workouts\\Services\\ProgramAssigner';

    public function __construct(
        private readonly TenantContext $context,
        private readonly MessagingService $messaging,
        private readonly TemplateRenderer $renderer,
        private readonly Notifier $notifier,
    ) {}

    /**
     * Fires a trigger for a client. Context keys: `dedupe` (occurrence key —
     * defaults per trigger), `days` (for N-day triggers) and any data used in
     * messages. Returns the runs created by this call (empty when deduplicated).
     *
     * @param  array<string, mixed>  $context
     * @return list<AutomationRun>
     */
    public function fire(string $trigger, Tenant $tenant, User $client, array $context = []): array
    {
        if (! in_array($trigger, self::TRIGGERS, true)) {
            throw new \InvalidArgumentException("Unknown automation trigger [$trigger].");
        }

        return $this->context->run($tenant, function () use ($trigger, $tenant, $client, $context) {
            $automations = Automation::query()->where('trigger', $trigger)->where('is_active', true)->orderBy('created_at')->get();
            if ($automations->isEmpty()) {
                return [];
            }
            $profile = ClientProfile::query()->where('user_id', $client->id)->first();
            $facts = $this->facts($client, $profile, $tenant);

            $runs = [];
            foreach ($automations as $automation) {
                if (! $this->matchesTriggerConfig($automation, $context)) {
                    continue;
                }
                if (! $this->conditionsPass($automation->conditions ?? [], $facts)) {
                    continue;
                }
                $run = $this->claim($automation, $client, $this->dedupeKey($automation, $trigger, $client, $context, $tenant));
                if ($run === null) {
                    continue;
                }
                $runs[] = $this->execute($run, $automation, $tenant, $client, $profile, $context);
            }

            return $runs;
        });
    }

    /**
     * Scheduled scan for time-based triggers (workout_missed, checkin_overdue,
     * birthday, package_expiring, program_started, program_finished).
     * Safe to run as often as needed: dedupe keys make every occurrence fire once.
     */
    public function scan(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $fired = 0;
        $tenantIds = Automation::withoutGlobalScopes()->where('is_active', true)
            ->whereIn('trigger', ['workout_missed', 'checkin_overdue', 'birthday', 'package_expiring', 'program_started', 'program_finished'])
            ->distinct()->pluck('tenant_id');

        foreach (Tenant::query()->whereIn('id', $tenantIds)->get() as $tenant) {
            $fired += $this->context->run($tenant, fn () => $this->scanTenant($tenant, $now));
        }

        return $fired;
    }

    private function scanTenant(Tenant $tenant, CarbonImmutable $now): int
    {
        $fired = 0;
        $automations = Automation::query()->where('is_active', true)->get()->groupBy('trigger');
        $clientIds = Membership::query()->where('role', 'client')->where('status', 'active')->pluck('user_id');
        $clients = User::query()->whereIn('id', $clientIds)->get()->keyBy('id');
        $today = $now->setTimezone($tenant->timezone ?: 'UTC')->toDateString();

        foreach ($automations->get('workout_missed', collect()) as $automation) {
            $days = max(1, (int) ($automation->trigger_config['days'] ?? 3));
            $active = ProgramAssignment::query()->where('status', 'active')->whereIn('client_id', $clientIds)->get()->keyBy('client_id');
            foreach ($active as $clientId => $assignment) {
                $client = $clients->get($clientId);
                if (! $client) {
                    continue;
                }
                $last = WorkoutSession::query()->where('client_id', $clientId)->where('status', 'completed')->latest('started_at')->first();
                $since = $last ? CarbonImmutable::instance($last->ended_at ?? $last->started_at) : CarbonImmutable::parse($assignment->start_date->toDateString(), $tenant->timezone ?: 'UTC');
                if ($since->greaterThan($now->subDays($days))) {
                    continue;
                }
                $fired += count($this->fire('workout_missed', $tenant, $client, [
                    'days' => $days,
                    'dedupe' => 'missed:'.$days.':'.($last?->id ?? 'none:'.$assignment->id),
                ]));
            }
        }

        foreach ($automations->get('checkin_overdue', collect()) as $automation) {
            $days = max(0, (int) ($automation->trigger_config['days'] ?? 1));
            $limit = CarbonImmutable::parse($today)->subDays($days)->toDateString();
            $overdue = CheckinResponse::query()->whereIn('status', ['pending', 'overdue'])->whereNotNull('due_on')->whereDate('due_on', '<', $limit)->get();
            foreach ($overdue as $response) {
                if ($client = $clients->get($response->client_id)) {
                    $fired += count($this->fire('checkin_overdue', $tenant, $client, ['days' => $days, 'dedupe' => 'checkin:'.$response->id]));
                }
            }
        }

        if ($automations->has('birthday')) {
            foreach (ClientProfile::query()->whereIn('user_id', $clientIds)->get() as $profile) {
                $client = $clients->get($profile->user_id);
                if (! $client || ! $profile->date_of_birth) {
                    continue;
                }
                try {
                    $dob = CarbonImmutable::parse($profile->date_of_birth);
                } catch (\Throwable) {
                    continue;
                }
                $local = $now->setTimezone($client->timezone ?: ($tenant->timezone ?: 'UTC'));
                if ($dob->format('m-d') === $local->format('m-d')) {
                    $fired += count($this->fire('birthday', $tenant, $client, ['dedupe' => 'birthday:'.$local->year]));
                }
            }
        }

        foreach ($automations->get('package_expiring', collect()) as $automation) {
            $days = max(1, (int) ($automation->trigger_config['days'] ?? 7));
            $expiring = CreditLedgerEntry::query()->where('amount', '>', 0)->whereNotNull('expires_on')
                ->whereDate('expires_on', '>=', $today)->whereDate('expires_on', '<=', CarbonImmutable::parse($today)->addDays($days)->toDateString())->get();
            foreach ($expiring as $entry) {
                $client = $clients->get($entry->client_id);
                if ($client && CreditLedgerEntry::balanceFor($client->id) > 0) {
                    $fired += count($this->fire('package_expiring', $tenant, $client, ['days' => $days, 'dedupe' => 'credits:'.$entry->id, 'expires_on' => $entry->expires_on?->toDateString()]));
                }
            }
        }

        if ($automations->has('program_started')) {
            $started = ProgramAssignment::query()->where('status', 'active')->whereDate('start_date', '<=', $today)
                ->whereDate('start_date', '>=', CarbonImmutable::parse($today)->subDays(2)->toDateString())->get();
            foreach ($started as $assignment) {
                if ($client = $clients->get($assignment->client_id)) {
                    $fired += count($this->fire('program_started', $tenant, $client, ['dedupe' => 'assignment:'.$assignment->id, 'program_id' => $assignment->program_id]));
                }
            }
        }

        if ($automations->has('program_finished')) {
            $finished = ProgramAssignment::query()->where('status', 'completed')->where('updated_at', '>=', $now->subDays(2))->get();
            foreach ($finished as $assignment) {
                if ($client = $clients->get($assignment->client_id)) {
                    $fired += count($this->fire('program_finished', $tenant, $client, ['dedupe' => 'assignment:'.$assignment->id, 'program_id' => $assignment->program_id]));
                }
            }
        }

        return $fired;
    }

    /**
     * Facts available to conditions.
     *
     * @return array<string, mixed>
     */
    public function facts(User $client, ?ClientProfile $profile, Tenant $tenant): array
    {
        $membership = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $client->id)->first();

        return [
            'goal' => $profile?->goal,
            'level' => $profile?->level,
            'gender' => $profile?->gender,
            'status' => $profile?->status,
            'risk_status' => $profile?->risk_status,
            'compliance_score' => $profile?->compliance_score,
            'tags' => array_values((array) ($profile->tags ?? [])),
            'coach_id' => $profile?->coach_id,
            'locale' => $client->preferredLocale(),
            'days_since_joined' => $membership?->joined_at ? (int) $membership->joined_at->diffInDays(now()) : null,
            'age' => $profile?->age(),
        ];
    }

    /**
     * @param  list<array{field: string, op: string, value: mixed}>  $conditions
     * @param  array<string, mixed>  $facts
     */
    public function conditionsPass(array $conditions, array $facts): bool
    {
        foreach ($conditions as $condition) {
            if (! $this->evaluate($facts[$condition['field'] ?? ''] ?? null, (string) ($condition['op'] ?? 'eq'), $condition['value'] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private function evaluate(mixed $actual, string $op, mixed $expected): bool
    {
        $norm = fn (mixed $v) => is_string($v) ? mb_strtolower($v) : $v;

        if (is_array($actual)) {
            $haystack = array_map($norm, $actual);
            $needles = array_map($norm, (array) $expected);

            return match ($op) {
                'contains', 'in', 'eq' => array_intersect($needles, $haystack) !== [],
                'not_contains', 'not_in', 'neq' => array_intersect($needles, $haystack) === [],
                default => false,
            };
        }

        return match ($op) {
            'eq' => $norm($actual) == $norm($expected),
            'neq' => $norm($actual) != $norm($expected),
            'in' => in_array($norm($actual), array_map($norm, (array) $expected), false),
            'not_in' => ! in_array($norm($actual), array_map($norm, (array) $expected), false),
            'contains' => is_string($actual) && str_contains($norm($actual), (string) $norm($expected)),
            'not_contains' => ! is_string($actual) || ! str_contains($norm($actual), (string) $norm($expected)),
            'gt' => is_numeric($actual) && $actual > $expected,
            'gte' => is_numeric($actual) && $actual >= $expected,
            'lt' => is_numeric($actual) && $actual < $expected,
            'lte' => is_numeric($actual) && $actual <= $expected,
            default => false,
        };
    }

    /** @param  array<string, mixed>  $context */
    private function matchesTriggerConfig(Automation $automation, array $context): bool
    {
        $days = $automation->trigger_config['days'] ?? null;
        if ($days !== null && isset($context['days']) && in_array($automation->trigger, ['workout_missed', 'checkin_overdue', 'package_expiring'], true)) {
            return (int) $context['days'] === (int) $days;
        }

        return true;
    }

    /** @param  array<string, mixed>  $context */
    private function dedupeKey(Automation $automation, string $trigger, User $client, array $context, Tenant $tenant): string
    {
        $occurrence = isset($context['dedupe']) ? (string) $context['dedupe'] : match ($trigger) {
            'client_joined' => 'joined',
            'birthday' => 'birthday:'.now($tenant->timezone ?: 'UTC')->year,
            'program_started', 'program_finished' => 'program:'.($context['program_assignment_id'] ?? $context['program_id'] ?? now()->toDateString()),
            'pr_hit' => 'pr:'.($context['record_id'] ?? now()->toIso8601String()),
            default => $trigger.':'.now($tenant->timezone ?: 'UTC')->toDateString(),
        };

        $key = $automation->id.':'.$client->id.':'.$occurrence;

        return strlen($key) > 255 ? $automation->id.':'.$client->id.':'.hash('sha256', $occurrence) : $key;
    }

    private function claim(Automation $automation, User $client, string $dedupeKey): ?AutomationRun
    {
        try {
            return DB::transaction(fn () => AutomationRun::create([
                'automation_id' => $automation->id,
                'client_id' => $client->id,
                'dedupe_key' => $dedupeKey,
                'status' => 'running',
            ]));
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }

    /** @param  array<string, mixed>  $context */
    private function execute(AutomationRun $run, Automation $automation, Tenant $tenant, User $client, ?ClientProfile $profile, array $context): AutomationRun
    {
        $results = [];
        $failed = false;
        foreach ($automation->actions ?? [] as $i => $action) {
            try {
                $results[] = ['type' => $action['type'] ?? null, 'ok' => true] + $this->perform($action, $automation, $tenant, $client, $profile, $context);
            } catch (\Throwable $e) {
                $failed = true;
                $results[] = ['type' => $action['type'] ?? null, 'ok' => false, 'error' => $e->getMessage()];
                Log::warning('automations.action_failed', ['automation' => $automation->id, 'action' => $i, 'client' => $client->id, 'error' => $e->getMessage()]);
            }
        }

        $run->update(['status' => $failed ? 'failed' : 'done', 'result' => ['trigger' => $automation->trigger, 'actions' => $results]]);

        return $run;
    }

    /**
     * @param  array<string, mixed>  $action
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function perform(array $action, Automation $automation, Tenant $tenant, User $client, ?ClientProfile $profile, array $context): array
    {
        $coach = $this->coachFor($tenant, $profile);

        switch ($action['type'] ?? null) {
            case 'send_message':
                if (! $coach) {
                    throw new \RuntimeException('No staff member available to send the message.');
                }
                $body = isset($action['template_id'])
                    ? (MessageTemplate::query()->findOrFail($action['template_id'])->body ?? [])
                    : ($action['body'] ?? []);
                $text = $this->renderer->render($body, $client, $tenant, array_map('strval', array_filter($context, 'is_scalar')));
                if ($text === '') {
                    throw new \RuntimeException('Empty message body.');
                }
                $conversation = $this->messaging->findOrCreateDirect($tenant, $coach, $client);
                $message = $this->messaging->send($conversation, $coach, ['type' => 'text', 'body' => $text, 'meta' => ['automation_id' => $automation->id]]);

                return ['message_id' => $message->id, 'conversation_id' => $conversation->id];

            case 'assign_habit':
                $habit = Habit::query()->findOrFail($action['habit_id'] ?? '');
                $existing = HabitAssignment::query()->where('client_id', $client->id)->where('habit_id', $habit->id)->where('is_active', true)->first();
                $assignment = $existing ?? HabitAssignment::create([
                    'habit_id' => $habit->id,
                    'client_id' => $client->id,
                    'schedule' => 'daily',
                    'target' => $habit->target,
                    'start_date' => now($client->timezone ?: ($tenant->timezone ?: 'UTC'))->toDateString(),
                    'is_active' => true,
                ]);

                return ['habit_assignment_id' => $assignment->id];

            case 'assign_program':
                $program = Program::query()->findOrFail($action['program_id'] ?? '');

                return ['program_assignment_id' => $this->assignProgram($program, $client, $coach, $tenant)];

            case 'notify_coach':
                if (! $coach) {
                    throw new \RuntimeException('No staff member to notify.');
                }
                $note = (string) ($action['message'] ?? '');
                $this->notifier->send($coach, $this->coachCategory($automation->trigger), 'automations.notify_coach.title', 'automations.notify_coach.body', [
                    'client' => $client->name,
                    'automation' => $automation->name,
                    'trigger' => __('automations.triggers.'.$automation->trigger),
                    'note' => $note,
                ], '/clients/'.$client->id, ['client_id' => $client->id, 'automation_id' => $automation->id]);

                return ['notified' => $coach->id];

            case 'add_tag':
                $tag = trim((string) ($action['tag'] ?? ''));
                if ($tag === '' || ! $profile) {
                    throw new \RuntimeException('Tag or client profile missing.');
                }
                $tags = array_values(array_unique([...(array) ($profile->tags ?? []), $tag]));
                $profile->update(['tags' => $tags]);

                return ['tags' => $tags];
        }

        throw new \InvalidArgumentException('Unknown action type.');
    }

    private function assignProgram(Program $program, User $client, ?User $coach, Tenant $tenant): string
    {
        $start = now($client->timezone ?: ($tenant->timezone ?: 'UTC'))->toDateString();
        if (class_exists(self::PROGRAM_ASSIGNER)) {
            // Workouts module: assign(Program, User $client, string $startDate, ?string $assignedBy, string $unlockMode)
            return (string) app(self::PROGRAM_ASSIGNER)->assign($program, $client, $start, $coach?->id)->id;
        }

        $existing = ProgramAssignment::query()->where('client_id', $client->id)->where('program_id', $program->id)->where('status', 'active')->first();

        return ($existing ?? ProgramAssignment::create([
            'program_id' => $program->id,
            'client_id' => $client->id,
            'assigned_by' => $coach?->id,
            'start_date' => $start,
            'status' => 'active',
            'unlock_mode' => 'all',
        ]))->id;
    }

    private function coachFor(Tenant $tenant, ?ClientProfile $profile): ?User
    {
        if ($profile?->coach_id && ($coach = User::query()->find($profile->coach_id))) {
            return $coach;
        }
        $ownerId = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'owner')->where('status', 'active')->value('user_id');

        return $ownerId ? User::query()->find($ownerId) : null;
    }

    private function coachCategory(string $trigger): string
    {
        return match ($trigger) {
            'workout_missed', 'pr_hit', 'program_started', 'program_finished' => 'workouts',
            'checkin_overdue' => 'checkins',
            'package_expiring', 'payment_failed' => 'payments',
            default => 'messages',
        };
    }
}
