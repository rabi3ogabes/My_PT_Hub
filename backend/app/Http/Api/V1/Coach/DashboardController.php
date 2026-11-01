<?php

namespace App\Http\Api\V1\Coach;

use App\Domain\Calendar\Models\Event;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Payment;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\ClientSummaryResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    public function show(): JsonResponse
    {
        $tenant = $this->tenant();
        $user = $this->user();
        $tz = $user->timezone ?: $tenant->timezone ?: 'UTC';
        $todayStart = now($tz)->startOfDay()->utc();
        $todayEnd = now($tz)->endOfDay()->utc();
        $monthStart = now($tz)->startOfMonth()->utc();

        $mrr = ClientSubscription::query()->whereIn('status', ['active', 'past_due'])->where('currency', $tenant->currency)->get()->sum(fn ($s) => $s->monthlyAmountMinor());
        $revenue = (int) Payment::query()->where('status', 'succeeded')->where('currency', $tenant->currency)->where('paid_at', '>=', $monthStart)->sum('amount_minor');

        $unread = ConversationParticipant::query()
            ->join('messages', 'messages.conversation_id', '=', 'conversation_participants.conversation_id')
            ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
            ->where('conversations.tenant_id', $tenant->id)
            ->where('conversation_participants.user_id', $user->id)
            ->where('messages.sender_id', '!=', $user->id)
            ->whereNull('messages.deleted_at')
            ->where(fn ($w) => $w->whereNull('conversation_participants.last_read_at')->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at'))
            ->count();

        $atRisk = ClientProfile::query()->with(['user', 'coach'])->where('status', 'active')->whereIn('risk_status', ['red', 'amber'])
            ->orderByRaw("case risk_status when 'red' then 0 else 1 end")->orderBy('compliance_score')->limit(8)->get();

        $sessionsToday = Event::query()->with('coach:id,name')->withCount(['bookings as booked_count' => fn ($q) => $q->whereIn('status', ['booked', 'attended'])])
            ->where('status', 'scheduled')->whereBetween('starts_at', [$todayStart, $todayEnd])->orderBy('starts_at')->get();

        $recent = WorkoutSession::query()->with('client:id,name')->where('status', 'completed')->latest('ended_at')->limit(6)->get()
            ->map(fn ($s) => ['type' => 'workout_completed', 'client' => ['id' => $s->client?->id, 'name' => $s->client?->name], 'text' => $s->name, 'at' => $s->ended_at?->toIso8601String()])
            ->concat(PersonalRecord::query()->with(['client:id,name', 'exercise'])->latest('achieved_at')->limit(4)->get()
                ->map(fn ($p) => ['type' => 'personal_record', 'client' => ['id' => $p->client?->id, 'name' => $p->client?->name], 'text' => $p->exercise?->translate('name'), 'at' => $p->achieved_at?->toIso8601String()]))
            ->concat(CheckinResponse::query()->with('client:id,name')->where('status', 'submitted')->latest('submitted_at')->limit(4)->get()
                ->map(fn ($c) => ['type' => 'checkin_submitted', 'client' => ['id' => $c->client?->id, 'name' => $c->client?->name], 'text' => null, 'at' => $c->submitted_at?->toIso8601String()]))
            ->sortByDesc('at')->values()->take(10);

        $money = fn (int $minor) => ['amount_minor' => $minor, 'currency' => $tenant->currency, 'formatted' => Money::format($minor, $tenant->currency)];

        return $this->ok([
            'kpis' => [
                'active_clients' => ClientProfile::query()->where('status', 'active')->count(),
                'at_risk' => ClientProfile::query()->where('status', 'active')->where('risk_status', 'red')->count(),
                'checkins_to_review' => CheckinResponse::query()->where('status', 'submitted')->count(),
                'unread_messages' => $unread,
                'sessions_today' => $sessionsToday->count(),
                'mrr' => $money((int) $mrr),
                'revenue_month' => $money($revenue),
            ],
            'at_risk_clients' => ClientSummaryResource::collection($atRisk)->resolve(),
            'todays_sessions' => $sessionsToday->map(fn (Event $e) => [
                'id' => $e->id, 'type' => $e->type, 'title' => $e->title, 'starts_at' => $e->starts_at->toIso8601String(), 'ends_at' => $e->ends_at->toIso8601String(),
                'capacity' => $e->capacity, 'booked_count' => (int) $e->booked_count, 'coach' => ['id' => $e->coach?->id, 'name' => $e->coach?->name],
            ]),
            'recent_activity' => $recent,
            'onboarding' => ['steps' => [
                ['key' => 'business_profile', 'done' => $tenant->onboarded_at !== null],
                ['key' => 'create_program', 'done' => Program::query()->where('tenant_id', $tenant->id)->exists()],
                ['key' => 'invite_client', 'done' => Invitation::query()->exists() || ClientProfile::query()->exists()],
                ['key' => 'connect_payments', 'done' => PaymentGateway::query()->where('is_active', true)->exists()],
                ['key' => 'publish_profile', 'done' => CoachProfile::query()->where('user_id', $user->id)->where('is_published', true)->exists()],
            ]],
        ]);
    }
}
