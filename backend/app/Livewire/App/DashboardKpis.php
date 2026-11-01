<?php

namespace App\Livewire\App;

use App\Domain\Tenancy\Models\ClientProfile;
use App\Livewire\App\Concerns\WithTenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * KPI tiles + at-risk clients + today's sessions, computed straight from the
 * module tables and always constrained to the active tenant.
 */
#[Lazy]
class DashboardKpis extends Component
{
    use WithTenant;

    /** @return array<string, int> */
    public function kpis(): array
    {
        $t = $this->tenant()->id;
        $uid = $this->user()->id;
        [$start, $end] = $this->today();

        return [
            'active_clients' => DB::table('tenant_user')->where('tenant_id', $t)->where('role', 'client')->where('status', 'active')->count(),
            'at_risk' => DB::table('client_profiles')->where('tenant_id', $t)->whereNull('deleted_at')->where('status', 'active')->whereIn('risk_status', ['red', 'amber'])->count(),
            'checkins' => DB::table('checkin_responses')->where('tenant_id', $t)->where('status', 'submitted')->count(),
            'unread' => DB::table('messages')
                ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
                ->join('conversation_participants as cp', function ($j) use ($uid) {
                    $j->on('cp.conversation_id', '=', 'messages.conversation_id')->where('cp.user_id', '=', $uid);
                })
                ->where('conversations.tenant_id', $t)
                ->whereNull('messages.deleted_at')
                ->whereNotNull('messages.sender_id')->where('messages.sender_id', '!=', $uid)
                ->where(fn ($q) => $q->whereNull('cp.last_read_at')->orWhereColumn('messages.created_at', '>', 'cp.last_read_at'))
                ->count(),
            'sessions_today' => $this->sessionsQuery($start, $end)->count(),
            'mrr_minor' => $this->mrr(),
        ];
    }

    /** Monthly recurring revenue in the tenant currency (minor units). */
    public function mrr(): int
    {
        $tenant = $this->tenant();

        return (int) round(DB::table('client_subscriptions')
            ->where('tenant_id', $tenant->id)->whereIn('status', ['active', 'past_due'])->where('currency', $tenant->currency)
            ->get(['amount_minor', 'interval', 'interval_count'])
            ->sum(function ($s) {
                $count = max(1, (int) $s->interval_count);

                return match ($s->interval) {
                    'year' => $s->amount_minor / (12 * $count),
                    'week' => $s->amount_minor * 52 / 12 / $count,
                    'day' => $s->amount_minor * 30 / $count,
                    default => $s->amount_minor / $count,
                };
            }));
    }

    /** @return array{0: Carbon, 1: Carbon} today's bounds in the tenant timezone, as UTC */
    private function today(): array
    {
        $tz = $this->tenant()->timezone ?: 'UTC';

        return [now($tz)->startOfDay()->utc(), now($tz)->endOfDay()->utc()];
    }

    private function sessionsQuery(Carbon $start, Carbon $end)
    {
        $q = DB::table('events')->where('events.tenant_id', $this->tenant()->id)
            ->whereNull('events.deleted_at')->where('events.status', 'scheduled')
            ->whereIn('events.type', ['session', 'class', 'live'])
            ->whereBetween('events.starts_at', [$start, $end]);

        return $this->isOwner() ? $q : $q->where('events.coach_id', $this->user()->id);
    }

    public function placeholder()
    {
        return view('livewire.app.dashboard-kpis-placeholder');
    }

    public function render()
    {
        [$start, $end] = $this->today();
        $tz = $this->tenant()->timezone ?: 'UTC';

        $atRisk = ClientProfile::query()->where('status', 'active')->whereIn('risk_status', ['red', 'amber'])
            ->with('user:id,name,avatar_path')
            ->orderByRaw("CASE risk_status WHEN 'red' THEN 0 ELSE 1 END")
            ->orderBy('compliance_score')->limit(6)->get();

        /** @var Collection<int, object> $sessions */
        $sessions = $this->sessionsQuery($start, $end)
            ->leftJoin('bookings', fn ($j) => $j->on('bookings.event_id', '=', 'events.id')->whereIn('bookings.status', ['booked', 'attended']))
            ->groupBy('events.id', 'events.title', 'events.starts_at', 'events.ends_at', 'events.meeting_url', 'events.capacity')
            ->orderBy('events.starts_at')
            ->limit(8)
            ->get(['events.id', 'events.title', 'events.starts_at', 'events.ends_at', 'events.meeting_url', 'events.capacity', DB::raw('COUNT(bookings.id) as booked')])
            ->map(function ($e) use ($tz) {
                $e->starts = Carbon::parse($e->starts_at, 'UTC')->setTimezone($tz);
                $e->ends = Carbon::parse($e->ends_at, 'UTC')->setTimezone($tz);

                return $e;
            });

        return view('livewire.app.dashboard-kpis', [
            'kpis' => $this->kpis(),
            'atRisk' => $atRisk,
            'sessions' => $sessions,
            'currency' => $this->tenant()->currency,
        ]);
    }
}
