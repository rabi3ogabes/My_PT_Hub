<?php

use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Services\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(fn () => Notification::fake());

it('never exposes another tenant\'s catalogue, orders, invoices or subscriptions', function () {
    [$a, $ownerA] = $this->coachIn('QA', 'QAR');
    [$b, $ownerB] = $this->coachIn('QA', 'QAR');
    $this->connectGateway($a, 'manual');
    [$productA, $priceA] = $this->makeProduct($a);
    $gatewayA = \App\Domain\Payments\Models\PaymentGateway::withoutGlobalScopes()->where('tenant_id', $a->id)->first();
    $orderA = $this->makeOrder($a, $productA, $priceA);
    app(PaymentService::class)->markPaid($orderA, ['driver' => 'manual']);
    $invoiceA = \App\Domain\Payments\Models\Invoice::withoutGlobalScopes()->where('order_id', $orderA->id)->first();

    $this->actingAsIn($ownerB, $b);
    $this->getJson("/api/v1/products/{$productA->id}")->assertNotFound();
    $this->getJson("/api/v1/orders/{$orderA->id}")->assertNotFound();
    $this->postJson("/api/v1/orders/{$orderA->id}/refund")->assertNotFound();
    $this->getJson("/api/v1/invoices/{$invoiceA->id}/pdf")->assertNotFound();
    $this->patchJson("/api/v1/payment-gateways/{$gatewayA->id}", ['label' => 'hijack'])->assertNotFound();
    expect($this->getJson('/api/v1/orders')->json('meta.total'))->toBe(0)
        ->and($this->getJson('/api/v1/products')->json('meta.total'))->toBe(0)
        ->and($this->getJson('/api/v1/invoices')->json('meta.total'))->toBe(0)
        ->and($this->getJson('/api/v1/analytics/overview')->json('data.revenue_gross.amount_minor'))->toBe(0);

    // Recording a manual payment against another tenant's product is impossible.
    $client = $this->addClient($b, $ownerB);
    $this->actingAsIn($ownerB, $b)->postJson('/api/v1/manual-payments', ['product_id' => $productA->id, 'price_id' => $priceA->id, 'client_id' => $client->id, 'method' => 'cash'])->assertNotFound();
});

it('cannot settle another tenant\'s order through its own gateway webhook', function () {
    Http::fake(['api.tap.company/*' => Http::response(['id' => 'chg_iso', 'transaction' => ['url' => 'https://tap.test']])]);
    [$a] = $this->coachIn('QA', 'QAR');
    [$b] = $this->coachIn('QA', 'QAR');
    $this->connectGateway($a, 'tap');
    $gatewayB = $this->connectGateway($b, 'tap');
    [$productA, $priceA] = $this->makeProduct($a);
    $orderA = $this->makeOrder($a, $productA, $priceA);

    // Tenant B's coach signs a "paid" event (with their own key) referencing tenant A's order.
    $data = ['id' => 'chg_x', 'amount' => 399, 'currency' => 'QAR', 'status' => 'CAPTURED', 'reference' => ['gateway' => 'g', 'payment' => 'p', 'order' => $orderA->id], 'transaction' => ['created' => '1']];
    $this->postWebhook($gatewayB, json_encode($data), ['hashstring' => \App\Domain\Payments\Gateways\Drivers\TapGateway::hashString($data, 'sk_test_tap')])->assertStatus(422);
    expect(Order::withoutGlobalScopes()->find($orderA->id)->status)->toBe('pending');
});

it('keeps client purchases private between clients of the same coach', function () {
    [$tenant, $owner] = $this->coachIn('QA', 'QAR');
    $this->connectGateway($tenant, 'manual');
    $alice = $this->addClient($tenant, $owner);
    $bob = $this->addClient($tenant, $owner);
    [$product, $price] = $this->makeProduct($tenant);
    app(PaymentService::class)->markPaid($this->makeOrder($tenant, $product, $price, ['client' => $alice]), ['driver' => 'manual']);

    expect($this->actingAsIn($bob, $tenant)->getJson('/api/v1/me/purchases')->json('data.orders'))->toBe([])
        ->and($this->actingAsIn($alice, $tenant)->getJson('/api/v1/me/purchases')->json('data.orders'))->toHaveCount(1);
    $this->actingAsIn($alice, $tenant)->getJson('/api/v1/orders')->assertForbidden();
});
