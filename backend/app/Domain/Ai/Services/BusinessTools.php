<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Services\Features\ChurnAdvisor;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\Program;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Predefined, tenant-scoped analytics functions the business assistant may
 * call. The model only chooses a tool and a period — it never writes SQL.
 * Every query filters on the tenant explicitly.
 */
final class BusinessTools
{
    public function __construct(private readonly ChurnAdvisor $churn) {}

    /** @return array{0: Carbon, 1: Carbon} */
    public function period(Tenant $tenant, string $period): array
    {
        $now = now()->setTimezone($tenant->timezone ?: 'UTC');
        $start = $period === 'last_month' ? $now->copy()->subMonthNoOverflow()->startOfMonth() : $now->copy()->startOfMonth();

        return [$start->copy()->utc(), $start->copy()->endOfMonth()->utc()];
    }

    /** @return array{answer: string, rows: list<array<string, mixed>>} */
    public function run(string $tool, Tenant $tenant, string $period = 'this_month', ?int $limit = null): array
    {
        $limit = max(1, min(100, $limit ?? 20));

        return match ($tool) {
            'unpaid_clients' => $this->unpaidClients($tenant, $period, $limit),
            'best_retention_program' => $this->bestRetentionProgram($tenant, $limit),
            'at_risk_clients' => $this->atRiskClients($tenant, $limit),
            'revenue' => $this->revenue($tenant, $period),
            'pending_checkins' => $this->pendingCheckins($tenant, $limit),
            default => ['answer' => __('ai.business.unsupported'), 'rows' => []],
        };
    }

    /** @return array{answer: string, rows: list<array<string, mixed>>} */
    public function unpaidClients(Tenant $tenant, string $period, int $limit): array
    {
        [$from, $to] = $this->period($tenant, $period);
        $rows = [];

        $pastDue = DB::table('client_subscriptions')
            ->join('users', 'users.id', '=', 'client_subscriptions.client_id')
            ->where('client_subscriptions.tenant_id', $tenant->id)
            ->where('client_subscriptions.status', 'past_due')
            ->get(['users.id', 'users.name', 'client_subscriptions.amount_minor', 'client_subscriptions.currency']);
        foreach ($pastDue as $r) {
            $rows[$r->id] = ['client' => ['id' => $r->id, 'name' => $r->name], 'reason' => 'past_due', 'amount' => $this->money((int) $r->amount_minor, $r->currency)];
        }

        $unpaidOrders = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.client_id')
            ->where('orders.tenant_id', $tenant->id)
            ->whereIn('orders.status', ['pending', 'failed'])
            ->whereBetween('orders.created_at', [$from, $to])
            ->get(['users.id', 'users.name', 'orders.total_minor', 'orders.currency', 'orders.status']);
        foreach ($unpaidOrders as $r) {
            $rows[$r->id] ??= ['client' => ['id' => $r->id, 'name' => $r->name], 'reason' => 'order_'.$r->status, 'amount' => $this->money((int) $r->total_minor, $r->currency)];
        }

        $openInvoices = DB::table('invoices')
            ->join('users', 'users.id', '=', 'invoices.client_id')
            ->where('invoices.tenant_id', $tenant->id)
            ->where('invoices.status', 'open')
            ->whereBetween('invoices.issued_at', [$from, $to])
            ->get(['users.id', 'users.name', 'invoices.total_minor', 'invoices.currency']);
        foreach ($openInvoices as $r) {
            $rows[$r->id] ??= ['client' => ['id' => $r->id, 'name' => $r->name], 'reason' => 'invoice_open', 'amount' => $this->money((int) $r->total_minor, $r->currency)];
        }

        $rows = array_slice(array_values($rows), 0, $limit);

        return ['answer' => trans_choice('ai.business.answer.unpaid_clients', count($rows), ['count' => count($rows)]), 'rows' => $rows];
    }

    /** @return array{answer: string, rows: list<array<string, mixed>>} */
    public function bestRetentionProgram(Tenant $tenant, int $limit): array
    {
        $stats = DB::table('program_assignments')
            ->where('tenant_id', $tenant->id)
            ->selectRaw("program_id, COUNT(*) as total, SUM(CASE WHEN status IN ('active','completed') THEN 1 ELSE 0 END) as retained")
            ->groupBy('program_id')
            ->get();
        $names = Program::query()->whereIn('id', $stats->pluck('program_id'))->get()->keyBy('id');

        $rows = $stats->map(fn ($s) => [
            'program' => ['id' => $s->program_id, 'name' => $names[$s->program_id]?->translate('name')],
            'assignments' => (int) $s->total,
            'retained' => (int) $s->retained,
            'retention_pct' => (int) round($s->retained / max(1, $s->total) * 100),
        ])->sortBy([['retention_pct', 'desc'], ['assignments', 'desc']])->values()->take($limit)->all();

        $best = $rows[0] ?? null;

        return [
            'answer' => $best ? __('ai.business.answer.best_retention_program', ['name' => $best['program']['name'], 'pct' => $best['retention_pct'], 'count' => $best['assignments']]) : __('ai.business.answer.no_data'),
            'rows' => $rows,
        ];
    }

    /** @return array{answer: string, rows: list<array<string, mixed>>} */
    public function atRiskClients(Tenant $tenant, int $limit): array
    {
        $rows = collect($this->churn->scores($tenant))->whereIn('level', ['red', 'amber'])->sortByDesc('score')->take($limit)
            ->map(fn ($r) => ['client' => $r['client'], 'score' => $r['score'], 'level' => $r['level'], 'reasons' => $r['reasons']])->values()->all();

        return ['answer' => trans_choice('ai.business.answer.at_risk_clients', count($rows), ['count' => count($rows)]), 'rows' => $rows];
    }

    /** @return array{answer: string, rows: list<array<string, mixed>>} */
    public function revenue(Tenant $tenant, string $period): array
    {
        [$from, $to] = $this->period($tenant, $period);
        $paid = DB::table('payments')->where('tenant_id', $tenant->id)->where('status', 'succeeded')->whereBetween('paid_at', [$from, $to])
            ->selectRaw('currency, SUM(amount_minor) as total, COUNT(*) as n')->groupBy('currency')->get()->keyBy('currency');
        $refunds = DB::table('refunds')->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->where('refunds.tenant_id', $tenant->id)->where('refunds.status', 'succeeded')->whereBetween('refunds.created_at', [$from, $to])
            ->selectRaw('payments.currency as currency, SUM(refunds.amount_minor) as total')->groupBy('payments.currency')->get()->keyBy('currency');

        $rows = [];
        foreach ($paid->keys()->merge($refunds->keys())->unique() as $currency) {
            $gross = (int) ($paid[$currency]->total ?? 0);
            $refunded = (int) ($refunds[$currency]->total ?? 0);
            $rows[] = [
                'currency' => $currency,
                'payments' => (int) ($paid[$currency]->n ?? 0),
                'gross' => $this->money($gross, $currency),
                'refunded' => $this->money($refunded, $currency),
                'net' => $this->money($gross - $refunded, $currency),
            ];
        }
        $summary = collect($rows)->map(fn ($r) => $r['net']['formatted'])->implode(', ');

        return [
            'answer' => $rows === [] ? __('ai.business.answer.no_revenue') : __('ai.business.answer.revenue', ['amount' => $summary]),
            'rows' => $rows,
        ];
    }

    /** @return array{answer: string, rows: list<array<string, mixed>>} */
    public function pendingCheckins(Tenant $tenant, int $limit): array
    {
        $rows = DB::table('checkin_responses')
            ->join('users', 'users.id', '=', 'checkin_responses.client_id')
            ->where('checkin_responses.tenant_id', $tenant->id)
            ->where('checkin_responses.status', 'submitted')
            ->orderBy('checkin_responses.submitted_at')
            ->limit($limit)
            ->get(['checkin_responses.id', 'users.id as client_id', 'users.name', 'checkin_responses.submitted_at'])
            ->map(fn ($r) => [
                'checkin_response_id' => $r->id,
                'client' => ['id' => $r->client_id, 'name' => $r->name],
                'submitted_at' => $r->submitted_at ? Carbon::parse($r->submitted_at)->toIso8601String() : null,
            ])->all();

        return ['answer' => trans_choice('ai.business.answer.pending_checkins', count($rows), ['count' => count($rows)]), 'rows' => $rows];
    }

    /** @return array{amount_minor: int, currency: string, formatted: string} */
    private function money(int $minor, string $currency): array
    {
        return ['amount_minor' => $minor, 'currency' => $currency, 'formatted' => Money::format($minor, $currency)];
    }
}
