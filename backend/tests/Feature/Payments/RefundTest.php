<?php

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Services\PaymentService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(fn () => Notification::fake());

it('refunds through the gateway and issues a credit note referencing the invoice', function () {
    Http::fake(['api.stripe.com/v1/refunds' => Http::response(['id' => 're_1', 'status' => 'succeeded'])]);
    [$tenant, $owner] = $this->coachIn('FR', 'EUR');
    $this->connectGateway($tenant, 'stripe');
    [$product, $price] = $this->makeProduct($tenant, ['fulfilment' => ['credits' => 5]], ['amount_minor' => 12000]);
    $order = $this->makeOrder($tenant, $product, $price);
    app(PaymentService::class)->markPaid($order, ['driver' => 'stripe', 'method' => 'card', 'gateway_payment_id' => 'pi_42']);

    $this->actingAsIn($owner, $tenant)->postJson("/api/v1/orders/{$order->id}/refund", ['amount_minor' => 6000, 'reason' => 'Injury'])
        ->assertOk()->assertJsonPath('data.status', 'partially_refunded')->assertJsonPath('data.refunded.amount_minor', 6000);
    Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://api.stripe.com/v1/refunds' && $r['payment_intent'] === 'pi_42' && (int) $r['amount'] === 6000);

    $credit = Invoice::withoutGlobalScopes()->where('order_id', $order->id)->where('type', 'credit_note')->firstOrFail();
    $original = Invoice::withoutGlobalScopes()->where('order_id', $order->id)->where('type', 'invoice')->firstOrFail();
    expect($credit->credit_note_for_id)->toBe($original->id)
        ->and($credit->total_minor)->toBe(6000)
        ->and($credit->tax_minor)->toBe(1000)
        ->and($credit->number)->toBe('CN-'.now()->format('Y').'-000001');

    $this->actingAsIn($owner, $tenant)->postJson("/api/v1/orders/{$order->id}/refund", ['revoke_access' => true])
        ->assertOk()->assertJsonPath('data.status', 'refunded');
    expect(Invoice::withoutGlobalScopes()->where('order_id', $order->id)->where('type', 'credit_note')->count())->toBe(2)
        ->and(Order::withoutGlobalScopes()->find($order->id)->revoked_at)->not->toBeNull()
        ->and(CreditLedgerEntry::withoutGlobalScopes()->where('client_id', $order->refresh()->client_id)->sum('amount'))->toBe(0);

    expectApiError($this->actingAsIn($owner, $tenant)->postJson("/api/v1/orders/{$order->id}/refund", ['amount_minor' => 1]), 422, 'not_refundable');
});

it('rejects refunds above the refundable amount and from coaches without payments.manage', function () {
    [$tenant, $owner] = $this->coachIn('QA', 'QAR');
    $this->connectGateway($tenant, 'manual');
    [$product, $price] = $this->makeProduct($tenant);
    $order = $this->makeOrder($tenant, $product, $price);
    app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'cash']);
    $coach = $this->addStaff($tenant, 'coach');

    expectApiError($this->actingAsIn($owner, $tenant)->postJson("/api/v1/orders/{$order->id}/refund", ['amount_minor' => 50000]), 422, 'invalid_amount');
    expectApiError($this->actingAsIn($coach, $tenant)->postJson("/api/v1/orders/{$order->id}/refund", []), 403, 'forbidden');
});

it('records refunds initiated in the Stripe dashboard from the charge.refunded webhook', function () {
    [$tenant, $owner] = $this->coachIn('FR', 'EUR');
    $gateway = $this->connectGateway($tenant, 'stripe');
    [$product, $price] = $this->makeProduct($tenant, [], ['amount_minor' => 10000]);
    $order = $this->makeOrder($tenant, $product, $price);
    app(PaymentService::class)->markPaid($order, ['driver' => 'stripe', 'gateway_payment_id' => 'pi_dash']);

    $payload = json_encode(['id' => 'evt_ref', 'type' => 'charge.refunded', 'data' => ['object' => ['id' => 'ch_1', 'payment_intent' => 'pi_dash', 'amount_refunded' => 10000, 'currency' => 'eur']]]);
    $t = time();
    $this->postWebhook($gateway, $payload, ['Stripe-Signature' => "t={$t},v1=".hash_hmac('sha256', "{$t}.{$payload}", 'whsec_test_secret')])->assertOk();

    expect($order->refresh()->status)->toBe('refunded')
        ->and(Invoice::withoutGlobalScopes()->where('order_id', $order->id)->where('type', 'credit_note')->value('total_minor'))->toBe(10000);
    unset($owner);
});
