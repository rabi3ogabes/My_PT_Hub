<?php

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Payments\Gateways\Drivers\TapGateway;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Services\FulfilmentService;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Payments\Services\SubscriptionService;
use App\Notifications\GenericNotification;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(function () {
    Notification::fake();
    $this->renewalStatus = 'CAPTURED';
    Http::fake([
        'api.tap.company/v2/tokens' => Http::response(['id' => 'tok_renew']),
        'api.tap.company/v2/charges' => function (HttpRequest $r) {
            return ($r['customer_initiated'] ?? true) === false
                ? Http::response(['id' => 'chg_R'.uniqid(), 'status' => $this->renewalStatus, 'source' => ['payment_method' => 'VISA']])
                : Http::response(['id' => 'chg_S1', 'status' => 'INITIATED', 'transaction' => ['url' => 'https://tap.test/pay']]);
        },
    ]);
    [$this->tenant, $this->owner] = $this->coachIn('QA', 'QAR');
    $this->gateway = $this->connectGateway($this->tenant, 'tap');
    [$this->product, $this->price] = $this->makeProduct($this->tenant, ['type' => 'membership', 'grace_days' => 3, 'fulfilment' => ['credits' => 4]],
        ['type' => 'recurring', 'interval' => 'month', 'amount_minor' => 30000]);
    $this->client = $this->addClient($this->tenant, $this->owner);

    $orderId = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/shop/{$this->product->id}/checkout", ['price_id' => $this->price->id])->assertCreated()->json('data.order_id');
    $data = ['id' => 'chg_S1', 'amount' => 300, 'currency' => 'QAR', 'status' => 'CAPTURED', 'reference' => ['gateway' => 'g', 'payment' => 'p', 'order' => $orderId],
        'transaction' => ['created' => '1'], 'customer' => ['id' => 'cus_tap'], 'card' => ['id' => 'card_tap'], 'source' => ['payment_method' => 'VISA']];
    $this->postWebhook($this->gateway, json_encode($data), ['hashstring' => TapGateway::hashString($data, 'sk_test_tap')])->assertOk();
    $this->order = Order::withoutGlobalScopes()->findOrFail($orderId);
    $this->sub = ClientSubscription::withoutGlobalScopes()->where('order_id', $orderId)->firstOrFail();
});

it('starts an active subscription with the saved card on payment', function () {
    expect($this->sub->status)->toBe('active')
        ->and($this->sub->client_id)->toBe($this->client->id)
        ->and($this->sub->gateway_customer_id)->toBe('cus_tap')
        ->and($this->sub->gateway_payment_method)->toBe('card_tap')
        ->and($this->sub->current_period_end->toDateString())->toBe(now()->addMonthNoOverflow()->toDateString());

    $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/purchases')->assertOk()
        ->assertJsonPath('data.subscriptions.0.id', $this->sub->id)
        ->assertJsonPath('data.subscriptions.0.product_name', '12-Week Transformation')
        ->assertJsonPath('data.subscriptions.0.amount.amount_minor', 30000)
        ->assertJsonPath('data.subscriptions.0.cancel_at_period_end', false)
        ->assertJsonPath('data.credits', 4);
});

it('client cancels in two taps and keeps access until the period ends', function () {
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/subscriptions/{$this->sub->id}/cancel")
        ->assertOk()->assertJsonPath('data.status', 'active')->assertJsonPath('data.cancel_at_period_end', true);

    $this->travelTo($this->sub->current_period_end->copy()->subDay());
    app(SubscriptionService::class)->expireEnded();
    app(SubscriptionService::class)->processRenewals();
    expect($this->sub->refresh()->status)->toBe('active')
        ->and(Order::withoutGlobalScopes()->where('client_subscription_id', $this->sub->id)->count())->toBe(1);

    $this->travelTo($this->sub->current_period_end->copy()->addMinute());
    app(SubscriptionService::class)->processRenewals();
    app(SubscriptionService::class)->expireEnded();
    expect($this->sub->refresh()->status)->toBe('cancelled')->and($this->sub->ended_reason)->toBe('cancelled')
        ->and(Order::withoutGlobalScopes()->where('client_subscription_id', $this->sub->id)->count())->toBe(1);

    app(FulfilmentService::class)->expireAccess();
    expect($this->order->refresh()->revoked_at)->toBeNull(); // grace period (3 days)

    $this->travel(4)->days();
    app(FulfilmentService::class)->expireAccess();
    expect($this->order->refresh()->revoked_at)->not->toBeNull()->and($this->sub->refresh()->revoked_at)->not->toBeNull();
});

it('does not let a client cancel someone else\'s subscription', function () {
    $other = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($other, $this->tenant)->postJson("/api/v1/me/subscriptions/{$this->sub->id}/cancel")->assertNotFound();
    expect($this->sub->refresh()->cancel_at_period_end)->toBeFalse();
});

it('renews with the saved card at period end without re-delivering content', function () {
    $this->travelTo($this->sub->current_period_end->copy()->addMinute());
    expect(app(SubscriptionService::class)->processRenewals())->toBe(1);

    $sub = $this->sub->refresh();
    expect($sub->status)->toBe('active')
        ->and($sub->current_period_end->isAfter(now()->addDays(25)))->toBeTrue()
        ->and(Order::withoutGlobalScopes()->where('client_subscription_id', $sub->id)->where('status', 'paid')->count())->toBe(2)
        ->and(Invoice::withoutGlobalScopes()->where('client_id', $this->client->id)->count())->toBe(2);
    // Membership renewals top up credits each period.
    expect(CreditLedgerEntry::withoutGlobalScopes()->where('client_id', $this->client->id)->sum('amount'))->toBe(8);
    Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/v2/tokens') && $r['saved_card']['card_id'] === 'card_tap' && $r['saved_card']['customer_id'] === 'cus_tap');
    expect(app(SubscriptionService::class)->processRenewals())->toBe(0);
});

it('runs dunning on failed renewals and auto-pauses after the final retry', function () {
    $this->renewalStatus = 'DECLINED';
    $this->travelTo($this->sub->current_period_end->copy()->addMinute());
    app(SubscriptionService::class)->processRenewals();
    $sub = $this->sub->refresh();
    expect($sub->status)->toBe('past_due')->and($sub->failed_attempts)->toBe(1)->and($sub->next_retry_at->toDateString())->toBe(now()->addDay()->toDateString());

    foreach ([1, 3, 5] as $days) {
        $this->travel($days)->days();
        $this->travel(1)->minutes();
        app(SubscriptionService::class)->processRenewals();
    }
    $sub->refresh();
    expect($sub->status)->toBe('paused')->and($sub->failed_attempts)->toBe(4)->and($sub->ended_reason)->toBe('payment_failed')->and($sub->next_retry_at)->toBeNull();
    Notification::assertSentTo($this->client, GenericNotification::class);

    // Staff resume after the client fixes their card.
    $this->renewalStatus = 'CAPTURED';
    $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/subscriptions/{$sub->id}/resume")->assertOk()->assertJsonPath('data.status', 'active');
    app(SubscriptionService::class)->processRenewals();
    expect($sub->refresh()->status)->toBe('active')->and($sub->failed_attempts)->toBe(0)->and($sub->current_period_end->isFuture())->toBeTrue();
});

it('lets staff pause/resume/cancel and records client pause requests', function () {
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/subscriptions/{$this->sub->id}/pause")->assertOk()->assertJsonPath('data.status', 'active');
    expect($this->sub->refresh()->pause_requested_at)->not->toBeNull();

    $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/subscriptions/{$this->sub->id}/pause")->assertOk()->assertJsonPath('data.status', 'paused');
    $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/subscriptions/{$this->sub->id}/resume")->assertOk()->assertJsonPath('data.status', 'active');
    $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/subscriptions/{$this->sub->id}/cancel", ['immediately' => true])->assertOk()->assertJsonPath('data.status', 'cancelled');
    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/subscriptions?filter[status]=cancelled')->assertOk()->assertJsonPath('data.0.id', $this->sub->id);
    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/subscriptions/{$this->sub->id}/cancel"), 422, 'subscription_ended');
});

it('advances native Stripe subscriptions from invoice.paid webhooks', function () {
    [$tenant] = $this->coachIn('FR', 'EUR');
    $gateway = $this->connectGateway($tenant, 'stripe');
    [$product, $price] = $this->makeProduct($tenant, ['type' => 'membership'], ['type' => 'recurring', 'interval' => 'month', 'amount_minor' => 4900]);
    $order = $this->makeOrder($tenant, $product, $price);
    app(PaymentService::class)->markPaid($order, ['driver' => 'stripe', 'gateway_payment_id' => 'pi_0', 'subscription_ref' => 'sub_123', 'customer_ref' => 'cus_9']);
    $sub = ClientSubscription::withoutGlobalScopes()->where('order_id', $order->id)->firstOrFail();
    expect($sub->gateway_subscription_id)->toBe('sub_123');

    $this->travelTo($sub->current_period_end->copy()->addHour());
    app(SubscriptionService::class)->processRenewals();
    // Stripe drives native renewals: Aurum never charges them itself.
    expect(Order::withoutGlobalScopes()->where('client_subscription_id', $sub->id)->count())->toBe(1);

    $payload = json_encode(['id' => 'evt_inv', 'type' => 'invoice.paid', 'data' => ['object' => ['id' => 'in_1', 'subscription' => 'sub_123', 'billing_reason' => 'subscription_cycle', 'amount_paid' => 4900, 'currency' => 'eur', 'payment_intent' => 'pi_2']]]);
    $t = time();
    $this->postWebhook($gateway, $payload, ['Stripe-Signature' => "t={$t},v1=".hash_hmac('sha256', "{$t}.{$payload}", 'whsec_test_secret')])->assertOk();
    $this->postWebhook($gateway, $payload, ['Stripe-Signature' => "t={$t},v1=".hash_hmac('sha256', "{$t}.{$payload}", 'whsec_test_secret')])->assertJsonPath('status', 'duplicate');

    expect($sub->refresh()->current_period_end->isFuture())->toBeTrue()
        ->and(Order::withoutGlobalScopes()->where('client_subscription_id', $sub->id)->where('status', 'paid')->count())->toBe(2);
});
