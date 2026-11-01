<?php

namespace App\Http\Api\V1\Coach;

use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Services\InvitationService;
use App\Domain\Tenancy\Services\MembershipService;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\ClientDetailResource;
use App\Http\Api\V1\Resources\ClientSummaryResource;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

/**
 * Coach-side client management. `{client}` is the client's USER id.
 */
class ClientController extends ApiController
{
    public function __construct(
        private readonly InvitationService $invitations,
        private readonly MembershipService $memberships,
    ) {}

    private function profile(string $clientId): ClientProfile
    {
        $profile = ClientProfile::query()->with(['user', 'coach'])->where('user_id', $clientId)->first();
        if (! $profile) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $this->authorizeCoachScope($profile);

        return $profile;
    }

    /** Assistants and coaches see all tenant clients; this hook exists for future per-coach restrictions. */
    private function authorizeCoachScope(ClientProfile $profile): void
    {
        if (! $this->user()->isStaffIn() && ! $this->user()->is_super_admin) {
            throw ApiException::forbidden();
        }
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'filter.status' => ['nullable', Rule::in(['active', 'paused', 'archived', 'lead'])],
            'filter.risk' => ['nullable', Rule::in(['green', 'amber', 'red'])],
            'filter.coach_id' => ['nullable', 'string'],
            'filter.tag' => ['nullable', 'string'],
            'filter.group_id' => ['nullable', 'string'],
            'sort' => ['nullable', Rule::in(['name', '-name', 'last_active_at', '-last_active_at', 'compliance_score', '-compliance_score', 'created_at', '-created_at'])],
        ]);

        $query = ClientProfile::query()
            ->with(['user', 'coach'])
            ->join('users', 'users.id', '=', 'client_profiles.user_id')
            ->select('client_profiles.*')
            ->where('client_profiles.status', $request->input('filter.status', 'active'))
            ->when($request->input('q'), fn (Builder $q, string $term) => $q->where(fn (Builder $w) => $w
                ->where('users.name', 'like', "%{$term}%")
                ->orWhere('users.email', 'like', "%{$term}%")
                ->orWhere('users.phone', 'like', "%{$term}%")))
            ->when($request->input('filter.risk'), fn (Builder $q, string $risk) => $q->where('client_profiles.risk_status', $risk))
            ->when($request->input('filter.coach_id'), fn (Builder $q, string $id) => $q->where('client_profiles.coach_id', $id))
            ->when($request->input('filter.tag'), fn (Builder $q, string $tag) => $q->whereJsonContains('client_profiles.tags', $tag))
            ->when($request->input('filter.group_id'), fn (Builder $q, string $g) => $q->whereIn('client_profiles.user_id', fn ($s) => $s->select('user_id')->from('client_group_members')->where('client_group_id', $g)));

        $sort = $request->input('sort', '-last_active_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        $query->orderBy($column === 'name' ? 'users.name' : ($column === 'last_active_at' ? 'users.last_active_at' : 'client_profiles.'.$column), $direction)
            ->orderBy('client_profiles.id');

        $page = $query->paginate($this->perPage());
        $this->decorate(collect($page->items()));

        return $this->paginated($page, ClientSummaryResource::class);
    }

    /** Adds active program and unread message counts without N+1 queries. */
    private function decorate(\Illuminate\Support\Collection $profiles): void
    {
        $ids = $profiles->pluck('user_id')->all();
        if ($ids === []) {
            return;
        }
        $assignments = ProgramAssignment::query()->with('program:id,name')
            ->whereIn('client_id', $ids)->whereIn('status', ['active', 'scheduled', 'paused'])
            ->latest('start_date')->get()->unique('client_id')->keyBy('client_id');

        $me = $this->user()->id;
        $unread = ConversationParticipant::query()
            ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
            ->join('conversation_participants as other', fn ($j) => $j->on('other.conversation_id', '=', 'conversations.id')->whereIn('other.user_id', $ids))
            ->join('messages', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.tenant_id', $this->tenant()->id)
            ->where('conversation_participants.user_id', $me)
            ->where('conversations.type', 'direct')
            ->whereColumn('messages.sender_id', 'other.user_id')
            ->whereNull('messages.deleted_at')
            ->where(fn ($w) => $w->whereNull('conversation_participants.last_read_at')->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at'))
            ->groupBy('other.user_id')
            ->selectRaw('other.user_id as client_id, count(*) as c')
            ->pluck('c', 'client_id');

        foreach ($profiles as $p) {
            $a = $assignments->get($p->user_id);
            $p->setAttribute('active_program', $a ? ['id' => $a->program_id, 'name' => $a->program?->translate('name'), 'assignment_id' => $a->id, 'status' => $a->status] : null);
            $p->setAttribute('unread_messages', (int) ($unread[$p->user_id] ?? 0));
        }
    }

    /** Invite a client (email/phone) — they join via the app with the code or link. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:190'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:32'],
            'program_id' => ['nullable', 'string', 'exists:programs,id'],
            'send' => ['boolean'],
        ]);

        if (! empty($data['email']) && ($existing = User::query()->where('email', strtolower($data['email']))->first()) && $existing->membershipFor($this->tenant()->id)) {
            throw ApiException::conflict('client_exists', __('clients_api.already_client'));
        }

        $invitation = $this->invitations->create($this->tenant(), $this->user(), [
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'name' => $data['name'],
            'role' => 'client',
            'payload' => array_filter(['program_id' => $data['program_id'] ?? null]),
            'send' => $data['send'] ?? true,
        ]);

        return $this->created($this->invitationPayload($invitation));
    }

    /** @return array<string, mixed> */
    public static function invitationPayload(\App\Domain\Tenancy\Models\Invitation $invitation): array
    {
        $link = url('/invite/'.$invitation->code);

        return [
            'id' => $invitation->id,
            'code' => $invitation->code,
            'name' => $invitation->name,
            'email' => $invitation->email,
            'phone' => $invitation->phone,
            'role' => $invitation->role,
            'link' => $link,
            'whatsapp_url' => 'https://wa.me/'.preg_replace('/\D+/', '', (string) $invitation->phone).'?text='.rawurlencode(__('clients_api.whatsapp_invite', ['link' => $link, 'code' => $invitation->code])),
            'qr_url' => URL::temporarySignedRoute('invite.qr', now()->addDays(14), ['code' => $invitation->code]),
            'expires_at' => $invitation->expires_at?->toIso8601String(),
            'accepted_at' => $invitation->accepted_at?->toIso8601String(),
            'created_at' => $invitation->created_at?->toIso8601String(),
        ];
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $result = $this->invitations->importCsv($this->tenant(), $this->user(), (string) file_get_contents($request->file('file')->getRealPath()));

        return $this->ok($result);
    }

    public function show(string $client): JsonResponse
    {
        $profile = $this->profile($client);
        $this->decorate(collect([$profile]));

        return $this->ok(array_merge((new ClientDetailResource($profile))->resolve(), [
            'stats' => $this->stats($profile),
        ]));
    }

    /** @return array<string, mixed> */
    private function stats(ClientProfile $profile): array
    {
        $id = $profile->user_id;
        $since = now()->subDays(30);

        return [
            'sessions_30d' => WorkoutSession::query()->where('client_id', $id)->where('status', 'completed')->where('started_at', '>=', $since)->count(),
            'last_session_at' => WorkoutSession::query()->where('client_id', $id)->where('status', 'completed')->max('started_at'),
            'prs_30d' => PersonalRecord::query()->where('client_id', $id)->where('achieved_at', '>=', $since)->count(),
            'food_log_days_30d' => FoodLog::query()->where('client_id', $id)->where('log_date', '>=', $since->toDateString())->distinct()->count('log_date'),
            'latest_weight_kg' => BodyMetric::query()->where('client_id', $id)->whereNotNull('weight_kg')->latest('measured_on')->value('weight_kg'),
            'pending_checkins' => CheckinResponse::query()->where('client_id', $id)->where('status', 'submitted')->count(),
        ];
    }

    public function update(Request $request, string $client): JsonResponse
    {
        $profile = $this->profile($client);
        $data = $request->validate([
            'coach_id' => ['nullable', 'string', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $this->tenant()->id)->whereIn('role', ['owner', 'coach'])],
            'status' => ['sometimes', Rule::in(['active', 'paused'])],
            'goal' => ['nullable', Rule::in(['fat_loss', 'muscle_gain', 'strength', 'performance', 'health', 'recomposition'])],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'gender' => ['nullable', Rule::in(['female', 'male', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'height_cm' => ['nullable', 'numeric', 'between:90,250'],
            'activity_level' => ['nullable', Rule::in(['sedentary', 'light', 'moderate', 'active', 'very_active'])],
            'injuries' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:4000'],
            'equipment' => ['nullable', 'array'],
            'equipment.*' => ['string', 'max:32'],
            'days_per_week' => ['nullable', 'integer', 'between:1,7'],
            'diet_tags' => ['nullable', 'array'],
            'diet_tags.*' => ['string', 'max:24'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:32'],
            'parq_flagged' => ['boolean'],
        ]);
        if (isset($data['date_of_birth'])) {
            $data['date_of_birth'] = CarbonImmutable::parse($data['date_of_birth'])->toDateString();
        }
        $profile->fill($data)->save();

        return $this->show($client);
    }

    /** Archives the client (access removed, data retained for export). */
    public function destroy(string $client): JsonResponse
    {
        $profile = $this->profile($client);
        $this->memberships->archive($this->tenant(), $profile->user);
        ProgramAssignment::query()->where('client_id', $client)->whereIn('status', ['active', 'scheduled'])->update(['status' => 'cancelled']);
        HabitAssignment::query()->where('client_id', $client)->update(['is_active' => false]);
        CheckinSchedule::query()->where('client_id', $client)->update(['is_active' => false]);

        return $this->noContent();
    }

    /**
     * Per-client schedule strip: assigned workouts, meals, habits and check-ins per day.
     */
    public function schedule(Request $request, string $client): JsonResponse
    {
        $profile = $this->profile($client);
        $data = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $tz = $profile->user->timezone ?: 'UTC';
        $from = CarbonImmutable::parse($data['from'] ?? now($tz)->startOfWeek()->toDateString());
        $to = CarbonImmutable::parse($data['to'] ?? $from->addDays(6)->toDateString());
        if ($from->diffInDays($to) > 62) {
            throw ApiException::invalid('range_too_large', __('clients_api.range_too_large'));
        }

        $assignment = ProgramAssignment::query()->with('program.weeks.days.workout:id,name,estimated_minutes')
            ->where('client_id', $client)->whereIn('status', ['active', 'scheduled', 'paused'])->latest('start_date')->first();
        $sessions = WorkoutSession::query()->where('client_id', $client)
            ->whereBetween('started_at', [$from->startOfDay()->setTimezone('UTC'), $to->endOfDay()->setTimezone('UTC')])
            ->get(['id', 'program_day_id', 'workout_id', 'status', 'started_at'])->groupBy(fn ($s) => $s->started_at->setTimezone($tz)->toDateString());
        $habits = HabitAssignment::query()->with('habit')->where('client_id', $client)->where('is_active', true)->get();
        $checkins = CheckinResponse::query()->where('client_id', $client)->whereBetween('due_on', [$from->toDateString(), $to->toDateString()])->get()->groupBy(fn ($c) => $c->due_on->toDateString());
        $logs = FoodLog::query()->where('client_id', $client)->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('log_date, sum(kcal) as kcal')->groupBy('log_date')->pluck('kcal', 'log_date');

        $days = [];
        for ($d = $from; $d->lte($to); $d = $d->addDay()) {
            $date = $d->toDateString();
            $programDay = $assignment ? $this->programDayFor($assignment, $d) : null;
            $daySessions = $sessions->get($date, collect());
            $days[] = [
                'date' => $date,
                'workout' => $programDay ? [
                    'program_day_id' => $programDay->id,
                    'workout_id' => $programDay->workout?->id,
                    'name' => $programDay->workout?->name ?? $programDay->title,
                    'is_rest' => (bool) $programDay->is_rest,
                    'status' => $daySessions->firstWhere('program_day_id', $programDay->id)?->status ?? ($d->isPast() && ! $d->isToday() ? 'missed' : 'pending'),
                ] : null,
                'extra_sessions' => $daySessions->whereNull('program_day_id')->map(fn ($s) => ['id' => $s->id, 'status' => $s->status])->values(),
                'habits' => $habits->filter(fn ($h) => $this->habitScheduled($h, $d))->map(fn ($h) => ['assignment_id' => $h->id, 'name' => $h->habit?->translate('name')])->values(),
                'checkins' => $checkins->get($date, collect())->map(fn ($c) => ['id' => $c->id, 'status' => $c->status])->values(),
                'kcal_logged' => isset($logs[$date]) ? round((float) $logs[$date]) : null,
            ];
        }

        return $this->ok(['from' => $from->toDateString(), 'to' => $to->toDateString(), 'assignment_id' => $assignment?->id, 'days' => $days]);
    }

    private function programDayFor(ProgramAssignment $assignment, CarbonImmutable $date): ?\App\Domain\Workouts\Models\ProgramDay
    {
        $start = CarbonImmutable::parse($assignment->start_date)->addDays((int) $assignment->shift_days);
        if ($date->lt($start)) {
            return null;
        }
        $offset = (int) $start->diffInDays($date);
        $weekNumber = intdiv($offset, 7) + 1;
        $dayNumber = ($offset % 7) + 1;
        $week = $assignment->program?->weeks->firstWhere('number', $weekNumber);

        return $week?->days->firstWhere('day_number', $dayNumber);
    }

    private function habitScheduled(HabitAssignment $h, CarbonImmutable $d): bool
    {
        if ($h->start_date && $d->lt($h->start_date)) {
            return false;
        }
        if ($h->end_date && $d->gt($h->end_date)) {
            return false;
        }

        return match ($h->schedule) {
            'days' => in_array($d->isoWeekday(), array_map('intval', $h->days ?? []), true),
            default => true,
        };
    }

    public function sessions(Request $request, string $client): JsonResponse
    {
        $this->profile($client);
        $page = WorkoutSession::query()->where('client_id', $client)->withCount('sets')->latest('started_at')->cursorPaginate($this->perPage(20));

        return response()->json([
            'data' => collect($page->items())->map(fn (WorkoutSession $s) => [
                'id' => $s->id, 'name' => $s->name, 'status' => $s->status, 'workout_id' => $s->workout_id,
                'started_at' => $s->started_at?->toIso8601String(), 'ended_at' => $s->ended_at?->toIso8601String(),
                'duration_seconds' => $s->duration_seconds, 'total_volume_kg' => $s->total_volume_kg,
                'perceived_effort' => $s->perceived_effort, 'mood' => $s->mood, 'sets_count' => $s->sets_count,
            ]),
            'meta' => ['per_page' => $page->perPage(), 'next_cursor' => $page->nextCursor()?->encode()],
            'links' => (object) [],
            'errors' => [],
        ]);
    }

    public function session(string $client, string $session): JsonResponse
    {
        $this->profile($client);
        $s = WorkoutSession::query()->with(['sets.exercise'])->where('client_id', $client)->findOrFail($session);
        $prs = PersonalRecord::query()->with('exercise')->whereIn('session_set_id', $s->sets->pluck('id'))->get();

        return $this->ok([
            'id' => $s->id, 'name' => $s->name, 'status' => $s->status, 'notes' => $s->notes,
            'started_at' => $s->started_at?->toIso8601String(), 'ended_at' => $s->ended_at?->toIso8601String(),
            'duration_seconds' => $s->duration_seconds, 'total_volume_kg' => $s->total_volume_kg,
            'perceived_effort' => $s->perceived_effort, 'mood' => $s->mood,
            'exercises' => $s->sets->groupBy('exercise_id')->map(fn ($sets) => [
                'exercise' => ['id' => $sets->first()->exercise?->id, 'name' => $sets->first()->exercise?->translate('name')],
                'sets' => $sets->sortBy('set_number')->values()->map(fn ($x) => $x->only(['id', 'set_number', 'set_type', 'reps', 'weight_kg', 'duration_seconds', 'distance_m', 'rpe', 'is_completed'])),
            ])->values(),
            'personal_records' => $prs->map(fn ($p) => ['id' => $p->id, 'exercise_name' => $p->exercise?->translate('name'), 'type' => $p->type, 'value' => $p->value, 'previous_value' => $p->previous_value]),
        ]);
    }

    public function nutrition(Request $request, string $client): JsonResponse
    {
        $profile = $this->profile($client);
        $tz = $profile->user->timezone ?: 'UTC';
        $data = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);
        $to = CarbonImmutable::parse($data['to'] ?? now($tz)->toDateString());
        $from = CarbonImmutable::parse($data['from'] ?? $to->subDays(13)->toDateString());

        $target = NutritionTarget::query()->where('client_id', $client)->where('effective_from', '<=', $to->toDateString())->latest('effective_from')->first();
        $days = FoodLog::query()->where('client_id', $client)->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('log_date, sum(kcal) kcal, sum(protein_g) protein_g, sum(carbs_g) carbs_g, sum(fat_g) fat_g, count(*) entries')
            ->groupBy('log_date')->orderBy('log_date')->get()
            ->map(fn ($r) => ['date' => CarbonImmutable::parse($r->log_date)->toDateString(), 'kcal' => round((float) $r->kcal), 'protein_g' => round((float) $r->protein_g), 'carbs_g' => round((float) $r->carbs_g), 'fat_g' => round((float) $r->fat_g), 'entries' => (int) $r->entries]);

        return $this->ok([
            'targets' => $target ? $target->only(['kcal', 'protein_g', 'carbs_g', 'fat_g', 'water_ml', 'day_type', 'effective_from']) : null,
            'days' => $days,
            'averages' => [
                'kcal' => $days->avg('kcal') ? round($days->avg('kcal')) : null,
                'protein_g' => $days->avg('protein_g') ? round($days->avg('protein_g')) : null,
                'logged_days' => $days->count(),
            ],
        ]);
    }

    public function progress(string $client): JsonResponse
    {
        $this->profile($client);
        $metrics = BodyMetric::query()->where('client_id', $client)->orderBy('measured_on')->get();
        $photos = ProgressPhoto::query()->where('client_id', $client)->latest('taken_on')->limit(60)->get();

        return $this->ok([
            'weight' => $metrics->whereNotNull('weight_kg')->map(fn ($m) => ['date' => $m->measured_on->toDateString(), 'weight_kg' => $m->weight_kg])->values(),
            'body_fat' => $metrics->whereNotNull('body_fat_pct')->map(fn ($m) => ['date' => $m->measured_on->toDateString(), 'value' => $m->body_fat_pct])->values(),
            'measurements' => $metrics->whereNotNull('measurements')->map(fn ($m) => ['date' => $m->measured_on->toDateString(), 'values' => $m->measurements])->values(),
            'photos' => $photos->map(fn ($p) => [
                'id' => $p->id, 'pose' => $p->pose, 'taken_on' => $p->taken_on->toDateString(),
                'url' => URL::temporarySignedRoute('api.v1.clients.progress-photos.show', now()->addMinutes(10), ['client' => $client, 'photo' => $p->id]),
            ]),
            'records' => PersonalRecord::query()->with('exercise')->where('client_id', $client)->latest('achieved_at')->limit(50)->get()
                ->map(fn ($p) => ['id' => $p->id, 'exercise_id' => $p->exercise_id, 'exercise_name' => $p->exercise?->translate('name'), 'type' => $p->type, 'value' => $p->value, 'previous_value' => $p->previous_value, 'achieved_at' => $p->achieved_at?->toIso8601String()]),
        ]);
    }

    public function compliance(string $client): JsonResponse
    {
        $profile = $this->profile($client);
        $service = 'App\\Domain\\Progress\\Services\\ComplianceService';
        if (class_exists($service) && method_exists($service, 'forClient')) {
            return $this->ok(app($service)->forClient($profile->user));
        }

        return $this->ok(['score' => $profile->compliance_score, 'risk_status' => $profile->risk_status]);
    }
}
