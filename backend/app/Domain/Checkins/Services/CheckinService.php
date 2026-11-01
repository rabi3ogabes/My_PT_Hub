<?php

namespace App\Domain\Checkins\Services;

use App\Domain\Checkins\Models\CheckinComment;
use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Identity\Models\User;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Progress\Services\ComplianceService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Notifications\GenericNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Check-in lifecycle: form validation, scheduling, due-response generation,
 * reminders, overdue escalation, submission (with context snapshot), review
 * and comment threads.
 */
final class CheckinService
{
    public const QUESTION_TYPES = ['short_text', 'long_text', 'scale', 'single_choice', 'multi_choice', 'number', 'photo', 'measurement', 'file'];

    public const MEASUREMENTS = ['neck', 'chest', 'waist', 'hips', 'arm_l', 'arm_r', 'thigh_l', 'thigh_r', 'calf_l', 'calf_r', 'shoulders', 'glutes'];

    public function __construct(
        private readonly TenantContext $context,
        private readonly ComplianceService $compliance,
    ) {}

    /**
     * Validates and normalises builder questions. Throws validation_failed with per-question errors.
     *
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    public function normalizeQuestions(array $questions): array
    {
        $errors = [];
        $ids = [];
        $out = [];
        foreach (array_values($questions) as $i => $q) {
            $path = "questions.$i";
            $q = is_array($q) ? $q : [];
            $id = (string) ($q['id'] ?? '');
            if ($id === '' || ! preg_match('/^[a-z0-9_\-]{1,40}$/i', $id)) {
                $errors["$path.id"][] = __('checkins_module.errors.question_id');
            } elseif (isset($ids[$id])) {
                $errors["$path.id"][] = __('checkins_module.errors.question_id_duplicate');
            }
            $ids[$id] = true;

            $type = (string) ($q['type'] ?? '');
            if (! in_array($type, self::QUESTION_TYPES, true)) {
                $errors["$path.type"][] = __('checkins_module.errors.question_type');
            }

            $label = $q['label'] ?? null;
            $validLabel = (is_string($label) && trim($label) !== '' && mb_strlen($label) <= 300)
                || (is_array($label) && $label !== [] && ! array_diff(array_keys($label), ['en', 'ar', 'fr', 'es']) && collect($label)->every(fn ($v) => is_string($v) && mb_strlen($v) <= 300));
            if (! $validLabel) {
                $errors["$path.label"][] = __('checkins_module.errors.question_label');
            }

            $item = ['id' => $id, 'type' => $type, 'label' => is_string($label) ? [app()->getLocale() => $label] : $label, 'required' => (bool) ($q['required'] ?? false)];

            if (in_array($type, ['single_choice', 'multi_choice'], true)) {
                $options = $q['options'] ?? null;
                if (! is_array($options) || count($options) < 2 || count($options) > 20 || collect($options)->contains(fn ($o) => ! is_string($o) || trim($o) === '' || mb_strlen($o) > 120)) {
                    $errors["$path.options"][] = __('checkins_module.errors.question_options');
                } else {
                    $item['options'] = array_values($options);
                }
            }
            if ($type === 'scale') {
                $min = (int) ($q['min'] ?? 1);
                $max = (int) ($q['max'] ?? 10);
                if ($min >= $max || $min < 0 || $max > 100) {
                    $errors["$path.max"][] = __('checkins_module.errors.question_scale');
                }
                $item += ['min' => $min, 'max' => $max];
            }
            if ($type === 'number') {
                if (isset($q['min'])) {
                    $item['min'] = (float) $q['min'];
                }
                if (isset($q['max'])) {
                    $item['max'] = (float) $q['max'];
                }
                if (isset($item['min'], $item['max']) && $item['min'] >= $item['max']) {
                    $errors["$path.max"][] = __('checkins_module.errors.question_scale');
                }
            }
            if (in_array($type, ['measurement', 'number'], true) && isset($q['unit'])) {
                $item['unit'] = mb_substr((string) $q['unit'], 0, 16);
            }
            $out[] = $item;
        }
        if ($out === []) {
            $errors['questions'][] = __('checkins_module.errors.questions_required');
        }
        if ($errors) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), $errors);
        }

        return $out;
    }

    /**
     * Creates (or replaces) the schedule of a form for a client.
     *
     * @param  array<string, mixed>  $data  frequency, weekday?, every_days?, due_time?, start_date?
     */
    public function schedule(CheckinForm $form, User $client, array $data): CheckinSchedule
    {
        $today = $this->today($client);
        $start = $data['start_date'] ?? $today;
        $schedule = CheckinSchedule::query()->where('checkin_form_id', $form->id)->where('client_id', $client->id)->first()
            ?? new CheckinSchedule(['checkin_form_id' => $form->id, 'client_id' => $client->id]);
        $schedule->fill([
            'frequency' => $data['frequency'],
            'weekday' => $data['frequency'] === 'weekly' ? (int) ($data['weekday'] ?? CarbonImmutable::parse($start)->dayOfWeekIso) : null,
            'every_days' => $data['frequency'] === 'every_n_days' ? (int) $data['every_days'] : null,
            'due_time' => $data['due_time'] ?? '09:00',
            'is_active' => true,
        ]);
        $schedule->next_due_on = $this->firstDueDate($schedule, $start);
        $schedule->save();

        return $schedule;
    }

    public function firstDueDate(CheckinSchedule $schedule, string $start): string
    {
        $date = CarbonImmutable::parse($start);

        return match ($schedule->frequency) {
            'weekly' => $date->addDays((((int) $schedule->weekday) - $date->dayOfWeekIso + 7) % 7)->toDateString(),
            'program_week_end' => $this->programWeekEnd($schedule->client_id, $date) ?? $date->addDays(6)->toDateString(),
            default => $date->toDateString(),
        };
    }

    public function nextDueDate(CheckinSchedule $schedule, string $after): string
    {
        $date = CarbonImmutable::parse($after);

        return match ($schedule->frequency) {
            'every_n_days' => $date->addDays(max(1, (int) $schedule->every_days))->toDateString(),
            'program_week_end' => $this->programWeekEnd($schedule->client_id, $date->addDay()) ?? $date->addDays(7)->toDateString(),
            default => $date->addWeek()->toDateString(),
        };
    }

    /** Last day of the client's current program week on/after a date. */
    private function programWeekEnd(string $clientId, CarbonImmutable $from): ?string
    {
        $assignment = ProgramAssignment::query()->where('client_id', $clientId)->where('status', 'active')
            ->whereDate('start_date', '<=', $from->toDateString())->orderByDesc('start_date')->first();
        if (! $assignment) {
            return null;
        }
        $start = CarbonImmutable::parse($assignment->start_date->toDateString())->addDays((int) $assignment->shift_days);
        $elapsed = max(0, (int) $start->diffInDays($from, false));

        return $start->addDays(intdiv($elapsed, 7) * 7 + 6)->toDateString();
    }

    public function today(User $client): string
    {
        return CarbonImmutable::now($client->timezone ?: 'UTC')->toDateString();
    }

    /**
     * Scheduler: creates pending responses for schedules that are due (client-local date) and
     * advances them. Runs per tenant.
     *
     * @return int responses created
     */
    public function generateDue(?Tenant $tenant = null): int
    {
        $created = 0;
        foreach ($this->tenants($tenant) as $t) {
            $created += $this->context->run($t, function () {
                $count = 0;
                $schedules = CheckinSchedule::query()->where('is_active', true)
                    ->whereDate('next_due_on', '<=', now()->addDay()->toDateString())
                    ->with(['client', 'form'])->get();
                foreach ($schedules as $schedule) {
                    if (! $schedule->client || ! $schedule->form || ! $this->isActiveClient($schedule->client_id)) {
                        continue;
                    }
                    $today = $this->today($schedule->client);
                    $guard = 0;
                    while ($schedule->next_due_on->toDateString() <= $today && $guard++ < 60) {
                        $due = $schedule->next_due_on->toDateString();
                        $response = DB::transaction(function () use ($schedule, $due) {
                            $exists = CheckinResponse::query()->where('checkin_schedule_id', $schedule->id)->whereDate('due_on', $due)->exists();
                            $response = $exists ? null : CheckinResponse::create([
                                'checkin_form_id' => $schedule->checkin_form_id,
                                'checkin_schedule_id' => $schedule->id,
                                'client_id' => $schedule->client_id,
                                'due_on' => $due,
                                'status' => $due < $this->today($schedule->client) ? 'overdue' : 'pending',
                            ]);
                            $schedule->next_due_on = $this->nextDueDate($schedule, $due);
                            $schedule->save();

                            return $response;
                        });
                        if ($response) {
                            $count++;
                            if ($response->status === 'pending') {
                                $this->notifyClient($schedule->client, 'due', $response, $schedule->form->name);
                            }
                        }
                    }
                }

                return $count;
            });
        }

        return $created;
    }

    /**
     * Scheduler: reminds clients of pending check-ins due today once their due time passed.
     *
     * @return int reminders sent
     */
    public function sendReminders(?Tenant $tenant = null): int
    {
        $sent = 0;
        foreach ($this->tenants($tenant) as $t) {
            $sent += $this->context->run($t, function () {
                $count = 0;
                $responses = CheckinResponse::query()->where('status', 'pending')->whereNull('reminded_at')
                    ->with(['client', 'form', 'schedule'])->get();
                foreach ($responses as $response) {
                    $client = $response->client;
                    if (! $client || ! $response->due_on) {
                        continue;
                    }
                    $now = CarbonImmutable::now($client->timezone ?: 'UTC');
                    $dueTime = substr((string) ($response->schedule?->due_time ?? '09:00'), 0, 5);
                    $dueAt = CarbonImmutable::parse($response->due_on->toDateString().' '.$dueTime, $client->timezone ?: 'UTC');
                    if ($now->lt($dueAt)) {
                        continue;
                    }
                    $this->notifyClient($client, 'reminder', $response, (string) $response->form?->name);
                    $response->forceFill(['reminded_at' => now()])->save();
                    $count++;
                }

                return $count;
            });
        }

        return $sent;
    }

    /**
     * Scheduler: marks pending responses overdue (day after due date, client-local) and
     * escalates them to the client's coach (or the owners).
     *
     * @return int responses marked overdue
     */
    public function markOverdue(?Tenant $tenant = null): int
    {
        $marked = 0;
        foreach ($this->tenants($tenant) as $t) {
            $marked += $this->context->run($t, function () {
                $count = 0;
                $responses = CheckinResponse::query()->whereIn('status', ['pending', 'overdue'])->whereNull('escalated_at')
                    ->with(['client', 'form'])->get();
                foreach ($responses as $response) {
                    if (! $response->client || ! $response->due_on || $response->due_on->toDateString() >= $this->today($response->client)) {
                        continue;
                    }
                    $response->forceFill(['status' => 'overdue', 'escalated_at' => now()])->save();
                    $coaches = $this->coachesOf($response->client_id);
                    if ($coaches->isNotEmpty()) {
                        Notification::send($coaches, new GenericNotification(
                            'checkins',
                            __('checkins_module.notifications.overdue_title', ['name' => $response->client->name]),
                            __('checkins_module.notifications.overdue_body', ['name' => $response->client->name, 'form' => (string) $response->form?->name, 'date' => $response->due_on->toDateString()]),
                            '/checkins/'.$response->id,
                            ['checkin_response_id' => $response->id, 'client_id' => $response->client_id],
                        ));
                    }
                    $count++;
                }

                return $count;
            });
        }

        return $marked;
    }

    /**
     * Client submission: validates answers, records body metric, links photos, snapshots context.
     *
     * @param  array<string, mixed>  $data  answers, weight_kg?, body_fat_pct?, measurements?, photo_ids?
     */
    public function submit(CheckinResponse $response, User $client, array $data): CheckinResponse
    {
        if ($response->client_id !== $client->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        if ($response->status === 'reviewed') {
            throw ApiException::conflict('checkin_already_reviewed', __('checkins_module.errors.already_reviewed'));
        }
        $form = $response->form()->withTrashed()->firstOrFail();
        $answers = $this->validateAnswers($form, $data['answers'] ?? [], $client->id);

        return DB::transaction(function () use ($response, $client, $data, $answers, $form) {
            $today = $this->today($client);
            $hasMetric = isset($data['weight_kg']) || isset($data['body_fat_pct']) || ! empty($data['measurements']);
            if ($hasMetric) {
                $metric = BodyMetric::query()->where('client_id', $client->id)->whereDate('measured_on', $today)->where('source', 'checkin')->first()
                    ?? new BodyMetric(['client_id' => $client->id, 'measured_on' => $today, 'source' => 'checkin']);
                $metric->fill(array_filter([
                    'weight_kg' => $data['weight_kg'] ?? null,
                    'body_fat_pct' => $data['body_fat_pct'] ?? null,
                    'measurements' => ! empty($data['measurements']) ? array_intersect_key($data['measurements'], array_flip(self::MEASUREMENTS)) + ($metric->measurements ?? []) : null,
                ], fn ($v) => $v !== null))->save();
            }

            $photoIds = array_values(array_unique(array_merge($data['photo_ids'] ?? [], $this->photoIdsFromAnswers($form, $answers))));
            if ($photoIds) {
                $owned = ProgressPhoto::query()->where('client_id', $client->id)->whereIn('id', $photoIds)->pluck('id')->all();
                if (count($owned) !== count($photoIds)) {
                    throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['photo_ids' => [__('checkins_module.errors.photo_not_found')]]);
                }
                ProgressPhoto::query()->whereIn('id', $owned)->update(['checkin_response_id' => $response->id]);
            }

            $response->fill([
                'answers' => $answers,
                'status' => 'submitted',
                'submitted_at' => now(),
                'context' => $this->snapshot($client, $response),
            ])->save();

            $coaches = $this->coachesOf($client->id);
            if ($coaches->isNotEmpty()) {
                Notification::send($coaches, new GenericNotification(
                    'checkins',
                    __('checkins_module.notifications.submitted_title', ['name' => $client->name]),
                    __('checkins_module.notifications.submitted_body', ['name' => $client->name, 'form' => $form->name]),
                    '/checkins/'.$response->id,
                    ['checkin_response_id' => $response->id, 'client_id' => $client->id],
                ));
            }

            return $response->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    public function validateAnswers(CheckinForm $form, mixed $answers, string $clientId): array
    {
        $answers = is_array($answers) ? $answers : [];
        $errors = [];
        $clean = [];
        foreach ($form->questions ?? [] as $q) {
            $id = $q['id'];
            $value = $answers[$id] ?? null;
            $key = "answers.$id";
            $empty = $value === null || $value === '' || $value === [];
            if ($empty) {
                if (! empty($q['required']) && $q['type'] !== 'photo') {
                    $errors[$key][] = __('validation.required', ['attribute' => $id]);
                }

                continue;
            }
            $ok = match ($q['type']) {
                'short_text' => is_string($value) && mb_strlen($value) <= 500,
                'long_text' => is_string($value) && mb_strlen($value) <= 5000,
                'scale' => is_numeric($value) && (int) $value == $value && $value >= ($q['min'] ?? 1) && $value <= ($q['max'] ?? 10),
                'number', 'measurement' => is_numeric($value) && (! isset($q['min']) || $value >= $q['min']) && (! isset($q['max']) || $value <= $q['max']),
                'single_choice' => is_string($value) && in_array($value, $q['options'] ?? [], true),
                'multi_choice' => is_array($value) && array_is_list($value) && ! array_diff($value, $q['options'] ?? []),
                'photo', 'file' => is_string($value) || (is_array($value) && array_is_list($value) && collect($value)->every(fn ($v) => is_string($v))),
                default => false,
            };
            if (! $ok) {
                $errors[$key][] = __('checkins_module.errors.invalid_answer');

                continue;
            }
            $clean[$id] = in_array($q['type'], ['scale'], true) ? (int) $value : (in_array($q['type'], ['number', 'measurement'], true) ? (float) $value : $value);
        }
        if ($errors) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), $errors);
        }

        return $clean;
    }

    /** @return list<string> */
    private function photoIdsFromAnswers(CheckinForm $form, array $answers): array
    {
        $ids = [];
        foreach ($form->questions ?? [] as $q) {
            if ($q['type'] === 'photo' && isset($answers[$q['id']])) {
                $ids = array_merge($ids, (array) $answers[$q['id']]);
            }
        }

        return array_values(array_filter($ids, 'is_string'));
    }

    /**
     * Context frozen at submission: latest metrics, compliance, photos.
     *
     * @return array<string, mixed>
     */
    public function snapshot(User $client, CheckinResponse $response): array
    {
        $metrics = BodyMetric::query()->where('client_id', $client->id)->orderByDesc('measured_on')->limit(2)->get();
        $compliance = $this->compliance->forClient($client);

        return [
            'captured_at' => now()->toIso8601String(),
            'metrics' => $metrics->map(fn (BodyMetric $m) => [
                'measured_on' => $m->measured_on->toDateString(),
                'weight_kg' => $m->weight_kg,
                'body_fat_pct' => $m->body_fat_pct,
                'measurements' => $m->measurements,
            ])->all(),
            'compliance' => array_intersect_key($compliance, array_flip(['score', 'risk_status', 'workouts_pct', 'nutrition_pct', 'habits_pct', 'checkins_pct'])),
            'photo_ids' => ProgressPhoto::query()->where('checkin_response_id', $response->id)->pluck('id')->all(),
        ];
    }

    public function review(CheckinResponse $response, User $coach, ?string $feedback): CheckinResponse
    {
        if (! in_array($response->status, ['submitted', 'reviewed'], true)) {
            throw ApiException::conflict('checkin_not_submitted', __('checkins_module.errors.not_submitted'));
        }
        $response->fill([
            'status' => 'reviewed',
            'reviewed_at' => now(),
            'reviewed_by' => $coach->id,
            'coach_feedback' => $feedback ?? $response->coach_feedback,
        ])->save();
        $response->loadMissing(['client', 'form']);
        if ($response->client) {
            $this->notifyClient($response->client, 'reviewed', $response, (string) $response->form?->name);
        }

        return $response;
    }

    public function comment(CheckinResponse $response, User $author, string $body): CheckinComment
    {
        $comment = CheckinComment::create(['checkin_response_id' => $response->id, 'user_id' => $author->id, 'body' => $body]);
        $response->loadMissing('client');
        $recipients = $author->id === $response->client_id ? $this->coachesOf($response->client_id) : collect([$response->client])->filter();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new GenericNotification(
                'checkins',
                __('checkins_module.notifications.comment_title', ['name' => $author->name]),
                mb_strimwidth($body, 0, 140, '…'),
                '/checkins/'.$response->id,
                ['checkin_response_id' => $response->id],
            ));
        }

        return $comment->load('user');
    }

    /** Previous submitted responses of the same client (most recent first). */
    public function previous(CheckinResponse $response, int $limit = 3): Collection
    {
        return CheckinResponse::query()->where('client_id', $response->client_id)->whereKeyNot($response->id)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->when($response->submitted_at, fn ($q) => $q->where('submitted_at', '<', $response->submitted_at))
            ->orderByDesc('submitted_at')->limit($limit)->with('form')->get();
    }

    /** @return Collection<int, User> */
    public function coachesOf(string $clientId): Collection
    {
        $coachId = ClientProfile::query()->where('user_id', $clientId)->value('coach_id');
        if ($coachId) {
            $coach = User::query()->find($coachId);
            if ($coach) {
                return collect([$coach]);
            }
        }
        $ownerIds = Membership::query()->where('role', 'owner')->where('status', 'active')->pluck('user_id');

        return User::query()->whereIn('id', $ownerIds)->get();
    }

    private function isActiveClient(string $clientId): bool
    {
        return Membership::query()->where('user_id', $clientId)->where('role', 'client')->where('status', 'active')->exists();
    }

    private function notifyClient(User $client, string $kind, CheckinResponse $response, string $formName): void
    {
        $client->notify(new GenericNotification(
            'checkins',
            __('checkins_module.notifications.'.$kind.'_title', ['form' => $formName], $client->preferredLocale()),
            __('checkins_module.notifications.'.$kind.'_body', ['form' => $formName, 'date' => $response->due_on?->toDateString()], $client->preferredLocale()),
            '/checkins/'.$response->id,
            ['checkin_response_id' => $response->id],
        ));
    }

    /** @return iterable<Tenant> */
    private function tenants(?Tenant $tenant): iterable
    {
        return $tenant ? [$tenant] : Tenant::query()->cursor();
    }
}
