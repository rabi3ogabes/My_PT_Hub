<?php

use App\Domain\Crm\Models\Lead;
use App\Domain\Payments\Jobs\SnapshotAnalytics;
use App\Domain\Payments\Models\AnalyticsDaily;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Services\AnalyticsService;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Tenancy\Models\ClientProfile;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(function () {
    Notification::fake();
    [$this->tenant, $this->owner] = $this->coachIn('QA', 'QAR');
    $this->connectGateway($this->tenant, 'manual');
    [$this->product, $this->price] = $this->makeProduct($this->tenant, ['type' => 'membership'], ['type' => 'recurring', 'interval' => 'month', 'amount_minor' => 30000]);
});

it('reports MRR equal to the sum of active subscriptions (monthly-normalised)', function () {
    $active = collect([
        $this->makeSubscription($this->tenant, $this->product, $this->price, ['amount_minor' => 30000]),
        $this->makeSubscription($this->tenant, $this->product, $this->price, ['amount_minor' => 120000, 'interval' => 'year']),
        $this->makeSubscription($this->tenant, $this->product, $this->price, ['amount_minor' => 5000, 'interval' => 'week']),
        $this->makeSubscription($this->tenant, $this->product, $this->price, ['amount_minor' => 20000, 'interval_count' => 2]),
    ]);
    $this->makeSubscription($this->tenant, $this->product, $this->price, ['status' => 'trialing', 'amount_minor' => 99900]);
    $this->makeSubscription($this->tenant, $this->product, $this->price, ['status' => 'cancelled', 'amount_minor' => 99900, 'ended_at' => now()->subDay(), 'ended_reason' => 'cancelled']);
    $this->makeSubscription($this->tenant, $this->product, $this->price, ['status' => 'paused', 'amount_minor' => 99900, 'paused_at' => now()->subDay()]);
    [$other] = $this->coachIn('QA', 'QAR');
    [$op, $opr] = $this->makeProduct($other);
    $this->makeSubscription($other, $op, $opr, ['amount_minor' => 77700]);

    $expected = 30000 + 10000 + 21667 + 10000;
    expect($active->sum(fn (ClientSubscription $s) => $s->monthlyAmountMinor()))->toBe($expected)
        ->and(app(AnalyticsService::class)->mrr($this->tenant))->toBe($expected);

    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/analytics/overview?from='.now()->subDays(7)->toDateString())
        ->assertOk()
        ->assertJsonPath('data.mrr.amount_minor', $expected)
        ->assertJsonPath('data.arr.amount_minor', $expected * 12)
        ->assertJsonPath('data.active_subscribers', 4)
        ->assertJsonPath('data.churned_clients', 1)
        ->assertJsonPath('data.churned_mrr.amount_minor', 99900)
        ->assertJsonPath('data.arpu.amount_minor', intdiv($expected, 4))
        ->assertJsonPath('data.currency', 'QAR');
});

it('computes revenue, refunds, outstanding manual invoices, credit liability and lead conversion', function () {
    $client = $this->addClient($this->tenant, $this->owner);
    [$pack, $packPrice] = $this->makeProduct($this->tenant, ['type' => 'credit_pack', 'fulfilment' => ['credits' => 10]], ['amount_minor' => 50000]);
    $paid = $this->makeOrder($this->tenant, $pack, $packPrice, ['client' => $client]);
    app(PaymentService::class)->markPaid($paid, ['driver' => 'manual', 'method' => 'cash']);
    app(PaymentService::class)->refund($paid->refresh(), 10000, 'partial', $this->owner);
    $this->makeOrder($this->tenant, $pack, $packPrice, ['email' => 'due@example.com', 'name' => 'Due']);
    $this->inTenant($this->tenant, function () {
        Lead::create(['name' => 'L1', 'converted_at' => now()]);
        Lead::create(['name' => 'L2']);
        Lead::create(['name' => 'L3']);
        Lead::create(['name' => 'L4', 'converted_at' => now()]);
    });

    $res = $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/analytics/overview')->assertOk();
    $res->assertJsonPath('data.revenue_gross.amount_minor', 50000)
        ->assertJsonPath('data.refunds.amount_minor', 10000)
        ->assertJsonPath('data.revenue_net.amount_minor', 40000)
        ->assertJsonPath('data.outstanding_manual.count', 1)
        ->assertJsonPath('data.outstanding_manual.amount.amount_minor', 50000)
        ->assertJsonPath('data.credit_liability', 10)
        ->assertJsonPath('data.leads.created', 4)
        ->assertJsonPath('data.leads.conversion_rate', 50)
        ->assertJsonPath('data.revenue_by_gateway.0.driver', 'manual')
        ->assertJsonPath('data.revenue_by_product.0.revenue_minor', 50000);
    expect($res->json('data.mrr_movement'))->toHaveCount(6);
});

it('calculates coach performance with commission rules and exports CSV', function () {
    $coach = $this->addStaff($this->tenant, 'coach');
    $client = $this->addClient($this->tenant, $coach);
    expect(ClientProfile::withoutGlobalScopes()->where('user_id', $client->id)->value('coach_id'))->toBe($coach->id);
    [$pkg, $pkgPrice] = $this->makeProduct($this->tenant, [], ['amount_minor' => 100000]);
    $order = $this->makeOrder($this->tenant, $pkg, $pkgPrice, ['client' => $client]);
    app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);

    $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/commission-rules', ['coach_id' => $coach->id, 'type' => 'percent_package', 'value' => 1500])
        ->assertOk()->assertJsonPath('data.value', 1500);
    expectApiError($this->actingAsIn($coach, $this->tenant)->postJson('/api/v1/commission-rules', ['coach_id' => $coach->id, 'type' => 'per_session', 'value' => 1]), 403, 'forbidden');

    $rows = collect($this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/analytics/coaches')->assertOk()->json('data'));
    $row = $rows->firstWhere('coach.id', $coach->id);
    expect($row['revenue_minor'])->toBe(100000)->and($row['commission_minor'])->toBe(15000)->and($row['active_clients'])->toBe(1);

    $csv = $this->actingAsIn($this->owner, $this->tenant)->get('/api/v1/analytics/export?type=payments');
    $csv->assertOk();
    expect($csv->headers->get('Content-Type'))->toContain('text/csv');
    $content = $csv->streamedContent();
    expect($content)->toContain('invoice_number')->toContain('INV-'.now()->format('Y').'-000001')->toContain('100000');

    $payout = $this->actingAsIn($this->owner, $this->tenant)->get('/api/v1/analytics/export?type=coaches')->streamedContent();
    expect($payout)->toContain('commission_minor')->toContain($coach->id)->toContain('15000');

    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/analytics/retention')->assertOk()->assertJsonPath('data.0.size', 1)->assertJsonPath('data.0.retention.0', 100);
});

it('materialises daily snapshots', function () {
    $this->makeSubscription($this->tenant, $this->product, $this->price, ['amount_minor' => 30000]);
    $order = $this->makeOrder($this->tenant, $this->product, $this->price);
    app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);

    (new SnapshotAnalytics(now()->toDateString()))->handle(app(AnalyticsService::class));
    (new SnapshotAnalytics(now()->toDateString()))->handle(app(AnalyticsService::class));

    $row = AnalyticsDaily::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->firstOrFail();
    expect(AnalyticsDaily::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(1)
        ->and((int) $row->mrr_minor)->toBe(60000)
        ->and((int) $row->revenue_minor)->toBe(30000)
        ->and($row->currency)->toBe('QAR');
});

it('requires analytics permission', function () {
    $assistant = $this->addStaff($this->tenant, 'assistant');
    expectApiError($this->actingAsIn($assistant, $this->tenant)->getJson('/api/v1/analytics/overview'), 403, 'forbidden');
});
