<?php

namespace App\Domain\Payments\Services;

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Crm\Models\Lead;
use App\Domain\Payments\Models\AnalyticsDaily;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\CommissionRule;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\Refund;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Business metrics for a tenant, all in integer minor units of the tenant currency.
 * MRR is the sum of the monthly-normalised amounts of active client subscriptions.
 */
final class AnalyticsService
{
    private const LIVE_STATUSES = ['active', 'trialing', 'past_due'];

    public function mrr(Tenant $tenant): int
    {
        return (int) ClientSubscription::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('currency', $tenant->currency)
            ->get()
            ->sum(fn (ClientSubscription $s) => $s->monthlyAmountMinor());
    }

    /** @return array<string, mixed> */
    public function overview(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $currency = $tenant->currency;
        $gross = (int) $this->payments($tenant)->whereBetween('paid_at', [$from, $to])->sum('amount_minor');
        $refunds = (int) $this->refunds($tenant)->whereBetween('refunds.created_at', [$from, $to])->sum('refunds.amount_minor');
        $tax = (int) Order::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('currency', $currency)
            ->whereIn('status', ['paid', 'partially_refunded', 'refunded'])->whereBetween('paid_at', [$from, $to])->sum('tax_minor');

        $subs = ClientSubscription::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('currency', $currency)->get();
        $mrr = (int) $subs->where('status', 'active')->sum(fn (ClientSubscription $s) => $s->monthlyAmountMinor());
        $newMrr = (int) $subs->filter(fn ($s) => $s->created_at->between($from, $to) && $s->status !== 'trialing')->sum(fn ($s) => $s->monthlyAmountMinor());
        $churned = $subs->filter(fn ($s) => $this->churnedBetween($s, $from, $to));
        $churnedMrr = (int) $churned->sum(fn ($s) => $s->monthlyAmountMinor());

        $liveClients = $subs->whereIn('status', self::LIVE_STATUSES)->pluck('client_id')->unique();
        $churnedClients = $churned->pluck('client_id')->unique()->diff($liveClients)->count();
        $subscribersAtStart = $subs->filter(fn ($s) => $s->created_at->lt($from) && (! $this->endedAt($s) || $this->endedAt($s)->gte($from)))->pluck('client_id')->unique()->count();
        $churnRate = $subscribersAtStart > 0 ? round($churnedClients / $subscribersAtStart * 100, 2) : 0.0;
        $activeSubscribers = $subs->where('status', 'active')->pluck('client_id')->unique()->count();

        $activeClients = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'client')->where('status', 'active')->count();
        $lifetimeRevenue = (int) $this->payments($tenant)->sum('amount_minor') - (int) $this->refunds($tenant)->sum('refunds.amount_minor');
        $payingClients = Order::withoutGlobalScopes()->where('tenant_id', $tenant->id)->whereNotNull('paid_at')->whereNotNull('client_id')->distinct()->count('client_id');

        $outstanding = Order::withoutGlobalScopes()->where('orders.tenant_id', $tenant->id)->where('orders.status', 'pending')->where('orders.currency', $currency)
            ->join('payment_gateways', 'payment_gateways.id', '=', 'orders.payment_gateway_id')->where('payment_gateways.driver', 'manual')
            ->selectRaw('count(*) as c, coalesce(sum(orders.total_minor), 0) as s')->first();

        $leads = Lead::withoutGlobalScopes()->where('tenant_id', $tenant->id)->whereBetween('created_at', [$from, $to]);
        $leadCount = (clone $leads)->count();
        $converted = (clone $leads)->whereNotNull('converted_at')->count();

        $money = fn (int $minor) => ['amount_minor' => $minor, 'currency' => $currency, 'formatted' => Money::format($minor, $currency)];

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'currency' => $currency,
            'revenue_gross' => $money($gross),
            'revenue_net' => $money($gross - $refunds - $tax),
            'tax_collected' => $money($tax),
            'refunds' => $money($refunds),
            'mrr' => $money($mrr),
            'arr' => $money($mrr * 12),
            'new_mrr' => $money($newMrr),
            'churned_mrr' => $money($churnedMrr),
            'net_new_mrr' => $money($newMrr - $churnedMrr),
            'active_clients' => $activeClients,
            'active_subscribers' => $activeSubscribers,
            'churned_clients' => $churnedClients,
            'churn_rate' => $churnRate,
            'arpu' => $money($activeSubscribers > 0 ? intdiv($mrr, $activeSubscribers) : 0),
            'ltv' => $money($payingClients > 0 ? intdiv(max(0, $lifetimeRevenue), $payingClients) : 0),
            'outstanding_manual' => ['count' => (int) ($outstanding->c ?? 0), 'amount' => $money((int) ($outstanding->s ?? 0))],
            'credit_liability' => max(0, (int) CreditLedgerEntry::withoutGlobalScopes()->where('tenant_id', $tenant->id)->sum('amount')),
            'leads' => ['created' => $leadCount, 'converted' => $converted, 'conversion_rate' => $leadCount > 0 ? round($converted / $leadCount * 100, 2) : 0.0],
            'mrr_movement' => $this->mrrMovement($tenant, 6),
            'revenue_by_product' => $this->revenueByProduct($tenant, $from, $to),
            'revenue_by_gateway' => $this->revenueByGateway($tenant, $from, $to),
        ];
    }

    /** @return list<array{month: string, mrr_minor: int, new_minor: int, churned_minor: int}> */
    public function mrrMovement(Tenant $tenant, int $months = 12): array
    {
        $subs = ClientSubscription::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('currency', $tenant->currency)->get();
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonthsNoOverflow($i);
            $end = $start->copy()->endOfMonth();
            $out[] = [
                'month' => $start->format('Y-m'),
                'mrr_minor' => (int) $subs->filter(fn ($s) => $this->activeAt($s, $end->isFuture() ? now() : $end))->sum(fn ($s) => $s->monthlyAmountMinor()),
                'new_minor' => (int) $subs->filter(fn ($s) => $s->created_at->between($start, $end) && $s->status !== 'trialing')->sum(fn ($s) => $s->monthlyAmountMinor()),
                'churned_minor' => (int) $subs->filter(fn ($s) => $this->churnedBetween($s, $start, $end))->sum(fn ($s) => $s->monthlyAmountMinor()),
            ];
        }

        return $out;
    }

    /** @return list<array{product_id: string, name: string, revenue_minor: int, orders: int}> */
    public function revenueByProduct(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.tenant_id', $tenant->id)->where('orders.currency', $tenant->currency)
            ->whereIn('orders.status', ['paid', 'partially_refunded'])
            ->whereBetween('orders.paid_at', [$from, $to])
            ->groupBy('order_items.product_id', 'products.name')
            ->selectRaw('order_items.product_id as product_id, products.name as name, sum(orders.total_minor) as revenue, count(distinct orders.id) as orders')
            ->orderByDesc('revenue')
            ->get();

        return $rows->map(function ($r) {
            $names = json_decode((string) $r->name, true);
            $name = is_array($names) ? ($names[app()->getLocale()] ?? $names['en'] ?? (array_values($names)[0] ?? '')) : (string) $r->name;

            return ['product_id' => $r->product_id, 'name' => (string) $name, 'revenue_minor' => (int) $r->revenue, 'orders' => (int) $r->orders];
        })->values()->all();
    }

    /** @return list<array{driver: string, revenue_minor: int, payments: int}> */
    public function revenueByGateway(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        return $this->payments($tenant)->whereBetween('paid_at', [$from, $to])
            ->groupBy('driver')->selectRaw('driver, sum(amount_minor) as revenue, count(*) as c')->orderByDesc('revenue')->get()
            ->map(fn ($r) => ['driver' => (string) $r->driver, 'revenue_minor' => (int) $r->revenue, 'payments' => (int) $r->c])->values()->all();
    }

    /**
     * Retention by start month: share of each cohort (first paid order month) still
     * paying (active subscription or a paid order) in each following month.
     *
     * @return list<array{cohort: string, size: int, retention: list<float>}>
     */
    public function retentionCohorts(Tenant $tenant, int $months = 12): array
    {
        $orders = Order::withoutGlobalScopes()->where('tenant_id', $tenant->id)->whereNotNull('paid_at')->whereNotNull('client_id')
            ->get(['client_id', 'paid_at']);
        $subs = ClientSubscription::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get()->groupBy('client_id');
        $paidMonths = $orders->groupBy('client_id')->map(fn (Collection $o) => $o->map(fn ($x) => $x->paid_at->format('Y-m'))->unique()->values());
        $firstMonth = $orders->groupBy('client_id')->map(fn (Collection $o) => $o->min('paid_at')->copy()->startOfMonth());
        $windowStart = now()->startOfMonth()->subMonthsNoOverflow($months - 1);

        $out = [];
        foreach ($firstMonth->filter(fn ($m) => $m->gte($windowStart))->groupBy(fn ($m) => $m->format('Y-m'), true)->sortKeys() as $cohort => $members) {
            $clientIds = $members->keys();
            $start = Carbon::createFromFormat('Y-m-d', $cohort.'-01')->startOfMonth();
            $size = $clientIds->count();
            $retention = [];
            for ($k = 0; $start->copy()->addMonthsNoOverflow($k)->lte(now()); $k++) {
                $monthStart = $start->copy()->addMonthsNoOverflow($k);
                $monthEnd = $monthStart->copy()->endOfMonth();
                $retained = $clientIds->filter(function ($id) use ($paidMonths, $subs, $monthStart, $monthEnd) {
                    if ($paidMonths[$id]->contains($monthStart->format('Y-m'))) {
                        return true;
                    }

                    return ($subs[$id] ?? collect())->contains(fn ($s) => $this->activeAt($s, $monthEnd->isFuture() ? now() : $monthEnd));
                })->count();
                $retention[] = $size > 0 ? round($retained / $size * 100, 1) : 0.0;
            }
            $out[] = ['cohort' => $cohort, 'size' => $size, 'retention' => $retention];
        }

        return $out;
    }

    /**
     * Revenue, retention, sessions and commission per coach.
     *
     * @return list<array<string, mixed>>
     */
    public function coaches(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $staff = $tenant->users()->wherePivotIn('role', ['owner', 'coach'])->wherePivot('status', 'active')->get();
        $rules = CommissionRule::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get()->groupBy('coach_id');
        $profiles = DB::table('client_profiles')->where('tenant_id', $tenant->id)->whereNotNull('coach_id')->get(['user_id', 'coach_id'])->groupBy('coach_id');
        $subs = ClientSubscription::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get()->groupBy('client_id');

        return $staff->map(function ($coach) use ($tenant, $from, $to, $rules, $profiles, $subs) {
            $clientIds = collect($profiles[$coach->id] ?? [])->pluck('user_id')->unique()->values();
            $revenue = $clientIds->isEmpty() ? 0 : (int) $this->payments($tenant)->join('orders', 'orders.id', '=', 'payments.order_id')
                ->whereIn('orders.client_id', $clientIds)->whereBetween('payments.paid_at', [$from, $to])->sum('payments.amount_minor');
            $refunds = $clientIds->isEmpty() ? 0 : (int) $this->refunds($tenant)->join('orders', 'orders.id', '=', 'payments.order_id')
                ->whereIn('orders.client_id', $clientIds)->whereBetween('refunds.created_at', [$from, $to])->sum('refunds.amount_minor');
            $sessions = DB::table('bookings')->join('events', 'events.id', '=', 'bookings.event_id')
                ->where('bookings.tenant_id', $tenant->id)->where('events.coach_id', $coach->id)->where('bookings.status', 'attended')
                ->whereBetween('events.starts_at', [$from, $to])->count();
            $everSubscribed = $clientIds->filter(fn ($id) => isset($subs[$id]));
            $stillActive = $everSubscribed->filter(fn ($id) => $subs[$id]->whereIn('status', self::LIVE_STATUSES)->isNotEmpty());
            $activeClients = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'client')->where('status', 'active')->whereIn('user_id', $clientIds)->count();

            $net = $revenue - $refunds;
            $commission = 0;
            foreach ($rules[$coach->id] ?? [] as $rule) {
                $commission += $rule->type === 'per_session' ? (int) $rule->value * $sessions : intdiv(max(0, $net) * (int) $rule->value, 10000);
            }

            return [
                'coach' => ['id' => $coach->id, 'name' => $coach->name],
                'role' => $coach->pivot->role,
                'active_clients' => $activeClients,
                'revenue_minor' => $revenue,
                'refunds_minor' => $refunds,
                'net_revenue_minor' => $net,
                'retention_rate' => $everSubscribed->count() > 0 ? round($stillActive->count() / $everSubscribed->count() * 100, 1) : null,
                'sessions_delivered' => $sessions,
                'commission_minor' => $commission,
                'commission_rules' => collect($rules[$coach->id] ?? [])->map(fn ($r) => ['id' => $r->id, 'type' => $r->type, 'value' => (int) $r->value])->values()->all(),
                'currency' => $tenant->currency,
            ];
        })->values()->all();
    }

    /** Materialises one analytics_daily row (idempotent upsert). */
    public function snapshot(Tenant $tenant, Carbon $date): AnalyticsDaily
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();
        $subs = ClientSubscription::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('currency', $tenant->currency)->get();
        $at = $end->isFuture() ? now() : $end;

        $values = [
            'revenue_minor' => (int) $this->payments($tenant)->whereBetween('paid_at', [$start, $end])->sum('amount_minor'),
            'refunds_minor' => (int) $this->refunds($tenant)->whereBetween('refunds.created_at', [$start, $end])->sum('refunds.amount_minor'),
            'mrr_minor' => (int) $subs->filter(fn ($s) => $this->activeAt($s, $at))->sum(fn ($s) => $s->monthlyAmountMinor()),
            'new_mrr_minor' => (int) $subs->filter(fn ($s) => $s->created_at->between($start, $end) && $s->status !== 'trialing')->sum(fn ($s) => $s->monthlyAmountMinor()),
            'churned_mrr_minor' => (int) $subs->filter(fn ($s) => $this->churnedBetween($s, $start, $end))->sum(fn ($s) => $s->monthlyAmountMinor()),
            'active_clients' => Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'client')->where('status', 'active')->where('created_at', '<=', $end)->count(),
            'new_clients' => Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'client')->whereBetween('created_at', [$start, $end])->count(),
            'churned_clients' => $subs->filter(fn ($s) => $this->churnedBetween($s, $start, $end))->pluck('client_id')->unique()->count(),
            'sessions_delivered' => DB::table('bookings')->join('events', 'events.id', '=', 'bookings.event_id')->where('bookings.tenant_id', $tenant->id)
                ->where('bookings.status', 'attended')->whereBetween('events.starts_at', [$start, $end])->count(),
            'credit_liability' => max(0, (int) CreditLedgerEntry::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('created_at', '<=', $end)->sum('amount')),
            'currency' => $tenant->currency,
        ];

        $row = AnalyticsDaily::withoutGlobalScopes()->where('tenant_id', $tenant->id)->whereDate('date', $start->toDateString())->first()
            ?? new AnalyticsDaily(['tenant_id' => $tenant->id, 'date' => $start->toDateString()]);
        $row->fill($values)->save();

        return $row;
    }

    /**
     * Rows for the accounting / payout CSV export.
     *
     * @return \Generator<int, list<string|int|null>>
     */
    public function exportRows(Tenant $tenant, string $type, Carbon $from, Carbon $to): \Generator
    {
        if ($type === 'coaches') {
            yield ['coach_id', 'coach_name', 'active_clients', 'revenue_minor', 'refunds_minor', 'net_revenue_minor', 'sessions_delivered', 'commission_minor', 'currency'];
            foreach ($this->coaches($tenant, $from, $to) as $row) {
                yield [$row['coach']['id'], $row['coach']['name'], $row['active_clients'], $row['revenue_minor'], $row['refunds_minor'], $row['net_revenue_minor'], $row['sessions_delivered'], $row['commission_minor'], $row['currency']];
            }

            return;
        }

        yield ['payment_id', 'paid_at', 'order_id', 'invoice_number', 'client', 'email', 'description', 'gateway', 'method', 'amount_minor', 'tax_minor', 'refunded_minor', 'currency'];
        $query = Payment::withoutGlobalScopes()->where('payments.tenant_id', $tenant->id)->where('payments.status', 'succeeded')
            ->whereBetween('payments.paid_at', [$from, $to])->with(['order.items', 'order.client', 'order.invoices', 'refunds'])->orderBy('payments.paid_at');
        foreach ($query->lazy(200) as $payment) {
            $order = $payment->order;
            yield [
                $payment->id,
                $payment->paid_at?->toIso8601String(),
                $order?->id,
                $order?->invoices->firstWhere('type', 'invoice')?->number,
                $order?->client?->name ?? $order?->customer_name,
                $order?->client?->email ?? $order?->customer_email,
                $order?->items->pluck('description')->implode(' | '),
                $payment->driver,
                $payment->method,
                $payment->amount_minor,
                $order?->tax_minor,
                (int) $payment->refunds->where('status', 'succeeded')->sum('amount_minor'),
                $payment->currency,
            ];
        }
    }

    private function payments(Tenant $tenant): Builder
    {
        return Payment::withoutGlobalScopes()->where('payments.tenant_id', $tenant->id)->where('payments.status', 'succeeded')->where('payments.currency', $tenant->currency);
    }

    private function refunds(Tenant $tenant): Builder
    {
        return Refund::withoutGlobalScopes()->where('refunds.tenant_id', $tenant->id)->where('refunds.status', 'succeeded')
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')->where('payments.currency', $tenant->currency);
    }

    private function endedAt(ClientSubscription $s): ?Carbon
    {
        if ($s->ended_at) {
            return $s->ended_at;
        }

        return $s->status === 'paused' && $s->ended_reason === 'payment_failed' ? $s->paused_at : null;
    }

    private function churnedBetween(ClientSubscription $s, Carbon $from, Carbon $to): bool
    {
        if ($s->ended_reason === 'completed') {
            return false;
        }
        $ended = $this->endedAt($s);

        return $ended !== null && $ended->between($from, $to);
    }

    private function activeAt(ClientSubscription $s, Carbon $at): bool
    {
        if ($s->created_at->gt($at) || ($s->status === 'trialing')) {
            return false;
        }
        $ended = $this->endedAt($s);
        if ($ended && $ended->lte($at)) {
            return false;
        }
        if ($s->status === 'paused' && $s->paused_at && $s->paused_at->lte($at)) {
            return false;
        }

        return true;
    }
}
