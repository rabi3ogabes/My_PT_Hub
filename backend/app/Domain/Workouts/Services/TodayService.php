<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\BookingSetting;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Messaging\Models\Message;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Nutrition\Models\WaterLog;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Workouts\Models\WorkoutSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Composes the client "Today" screen payload (GET /me/today). */
class TodayService
{
    public const DEFAULT_WATER_ML = 2500;

    public function __construct(private readonly ClientProgram $program) {}

    /** @return array<string, mixed> */
    public function build(User $client, CarbonImmutable $date): array
    {
        $workout = $this->program->todayWorkout($client, $date);

        return [
            'date' => $date->toDateString(),
            'greeting_name' => $client->firstName(),
            'coach' => $this->coach($client),
            'workout' => $workout,
            'streak' => $this->streak($client, $date),
            'next_session' => $this->nextSession($client),
            'nutrition' => $this->nutrition($client, $date, $workout !== null),
            'habits' => $this->habits($client, $date),
            'checkin_due' => $this->checkinDue($client),
            'unread_messages' => $this->unreadMessages($client),
        ];
    }

    /** @return array{id: string, name: string, avatar_url: ?string}|null */
    public function coach(User $client): ?array
    {
        $profile = ClientProfile::query()->where('user_id', $client->id)->with('coach')->first();
        $coach = $profile?->coach;

        return $coach ? [
            'id' => $coach->id,
            'name' => $coach->name,
            'avatar_url' => $coach->avatar_path ? Storage::disk('public')->url($coach->avatar_path) : null,
        ] : null;
    }

    /**
     * Consecutive local days with activity (completed workout, food log or completed habit),
     * ending today (or yesterday, so the streak is not lost before today's first log).
     *
     * @return array{current: int, best: int}
     */
    public function streak(User $client, CarbonImmutable $today): array
    {
        $tz = $client->timezone ?: 'UTC';
        $from = $today->subDays(365);

        $days = WorkoutSession::query()->where('client_id', $client->id)->where('status', 'completed')
            ->where('started_at', '>=', $from->subDay()->utc())->pluck('started_at')
            ->map(fn ($t) => CarbonImmutable::parse($t)->setTimezone($tz)->toDateString());
        $days = $days->merge(FoodLog::query()->where('client_id', $client->id)->where('log_date', '>=', $from->toDateString())
            ->distinct()->pluck('log_date')->map(fn ($d) => CarbonImmutable::parse($d)->toDateString()));
        $assignmentIds = HabitAssignment::query()->where('client_id', $client->id)->pluck('id');
        if ($assignmentIds->isNotEmpty()) {
            $days = $days->merge(HabitLog::query()->whereIn('habit_assignment_id', $assignmentIds)->where('completed', true)
                ->where('log_date', '>=', $from->toDateString())->distinct()->pluck('log_date')
                ->map(fn ($d) => CarbonImmutable::parse($d)->toDateString()));
        }
        $set = $days->unique()->flip();

        $current = 0;
        $cursor = $set->has($today->toDateString()) ? $today : $today->subDay();
        while ($set->has($cursor->toDateString())) {
            $current++;
            $cursor = $cursor->subDay();
        }

        $best = 0;
        $run = 0;
        $prev = null;
        foreach ($set->keys()->sort()->values() as $day) {
            $d = CarbonImmutable::parse($day);
            $run = ($prev && $prev->addDay()->toDateString() === $d->toDateString()) ? $run + 1 : 1;
            $best = max($best, $run);
            $prev = $d;
        }

        return ['current' => $current, 'best' => max($best, $current)];
    }

    /** Next upcoming booking (Booking shape). @return array<string, mixed>|null */
    public function nextSession(User $client): ?array
    {
        $booking = Booking::query()
            ->where('client_id', $client->id)
            ->whereIn('status', ['booked', 'waitlisted'])
            ->whereHas('event', fn ($q) => $q->where('starts_at', '>=', now())->where('status', '!=', 'cancelled'))
            ->with(['event.coach', 'event.location'])
            ->get()
            ->sortBy(fn (Booking $b) => $b->event->starts_at)
            ->first();

        return $booking ? self::booking($booking) : null;
    }

    /** @return array<string, mixed> */
    public static function booking(Booking $booking): array
    {
        $event = $booking->event;
        $counts = $event ? Booking::query()->withoutGlobalScopes()->where('event_id', $event->id)
            ->selectRaw("sum(case when status in ('booked','attended') then 1 else 0 end) as booked, sum(case when status = 'waitlisted' then 1 else 0 end) as waitlisted")
            ->first() : null;
        $window = $event ? (int) (BookingSetting::query()->withoutGlobalScopes()->where('tenant_id', $event->tenant_id)
            ->where('coach_id', $event->coach_id)->value('cancel_window_hours') ?? 24) : 24;

        return [
            'id' => $booking->id,
            'event' => $event ? [
                'id' => $event->id,
                'type' => $event->type,
                'title' => $event->title,
                'description' => $event->description,
                'starts_at' => $event->starts_at?->toIso8601String(),
                'ends_at' => $event->ends_at?->toIso8601String(),
                'timezone' => $event->timezone,
                'capacity' => (int) $event->capacity,
                'booked_count' => (int) ($counts->booked ?? 0),
                'waitlist_count' => (int) ($counts->waitlisted ?? 0),
                'credits_cost' => (int) $event->credits_cost,
                'coach' => $event->coach ? ['id' => $event->coach->id, 'name' => $event->coach->name] : null,
                'location' => $event->location ? ['id' => $event->location->id, 'name' => $event->location->name] : null,
                'meeting_url' => $event->meeting_url,
                'rrule' => $event->rrule,
                'status' => $event->status,
            ] : null,
            'status' => $booking->status,
            'waitlist_position' => $booking->waitlist_position,
            'checkin_code' => $booking->checkin_code,
            'can_cancel_free_until' => $event?->starts_at?->copy()->subHours($window)->toIso8601String(),
            'created_at' => $booking->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function nutrition(User $client, CarbonImmutable $date, bool $trainingDay): array
    {
        $targets = NutritionTarget::query()->where('client_id', $client->id)
            ->where('effective_from', '<=', $date->toDateString())
            ->whereIn('day_type', ['all', $trainingDay ? 'training' : 'rest'])
            ->orderByDesc('effective_from')
            ->orderByRaw("case when day_type = 'all' then 1 else 0 end")
            ->first();

        $consumed = FoodLog::query()->where('client_id', $client->id)->whereDate('log_date', $date->toDateString())
            ->selectRaw('coalesce(sum(kcal),0) as kcal, coalesce(sum(protein_g),0) as protein_g, coalesce(sum(carbs_g),0) as carbs_g, coalesce(sum(fat_g),0) as fat_g')
            ->first();

        return [
            'targets' => $targets ? [
                'kcal' => (float) $targets->kcal,
                'protein_g' => (float) $targets->protein_g,
                'carbs_g' => (float) $targets->carbs_g,
                'fat_g' => (float) $targets->fat_g,
            ] : null,
            'consumed' => [
                'kcal' => round((float) ($consumed->kcal ?? 0), 1),
                'protein_g' => round((float) ($consumed->protein_g ?? 0), 1),
                'carbs_g' => round((float) ($consumed->carbs_g ?? 0), 1),
                'fat_g' => round((float) ($consumed->fat_g ?? 0), 1),
            ],
            'water_ml' => (int) WaterLog::query()->where('client_id', $client->id)->whereDate('log_date', $date->toDateString())->sum('ml'),
            'water_target_ml' => (int) ($targets->water_ml ?? self::DEFAULT_WATER_ML),
        ];
    }

    /** @return array{completed: int, total: int} */
    public function habits(User $client, CarbonImmutable $date): array
    {
        $assignments = HabitAssignment::query()->where('client_id', $client->id)->where('is_active', true)
            ->where('start_date', '<=', $date->toDateString())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $date->toDateString()))
            ->get()
            ->filter(fn (HabitAssignment $a) => $a->schedule !== 'days' || in_array($date->isoWeekday(), array_map('intval', $a->days ?? []), true));
        if ($assignments->isEmpty()) {
            return ['completed' => 0, 'total' => 0];
        }
        $completed = HabitLog::query()->whereIn('habit_assignment_id', $assignments->pluck('id'))
            ->whereDate('log_date', $date->toDateString())->where('completed', true)->count();

        return ['completed' => $completed, 'total' => $assignments->count()];
    }

    /** @return array{id: string, due_on: ?string, form_name: ?string}|null */
    public function checkinDue(User $client): ?array
    {
        $response = CheckinResponse::query()->where('client_id', $client->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->with('form')
            ->orderByRaw('due_on is null')->orderBy('due_on')
            ->first();

        return $response ? [
            'id' => $response->id,
            'due_on' => $response->due_on?->toDateString(),
            'form_name' => $response->form?->name,
        ] : null;
    }

    public function unreadMessages(User $client): int
    {
        $participants = ConversationParticipant::query()
            ->where('user_id', $client->id)
            ->whereHas('conversation', fn ($q) => $q) // tenant-scoped through the conversation
            ->get(['conversation_id', 'last_read_at']);
        if ($participants->isEmpty()) {
            return 0;
        }

        return (int) Message::query()
            ->where(function ($q) use ($participants) {
                foreach ($participants as $p) {
                    $q->orWhere(function ($w) use ($p) {
                        $w->where('conversation_id', $p->conversation_id);
                        if ($p->last_read_at) {
                            $w->where('created_at', '>', $p->last_read_at);
                        }
                    });
                }
            })
            ->where(fn ($q) => $q->whereNull('sender_id')->orWhere('sender_id', '!=', $client->id))
            ->where('type', '!=', 'system')
            ->count(DB::raw('1'));
    }
}
