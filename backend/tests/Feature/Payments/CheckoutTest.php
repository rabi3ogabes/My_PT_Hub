<?php

use App\Domain\Payments\Models\Coupon;
use App\Domain\Payments\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(function () {
    Notification::fake();
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_X', 'url' => 'https://checkout.stripe.com/c/pay/cs_X'])]);
});

it('shows a branded public checkout with tax-aware quotes', function () {
    [$tenant] = $this->coachIn('SA', 'SAR');
    $this->connectGateway($tenant, 'stripe');
    [$product, $price] = $this->makeProduct($tenant, [], ['amount_minor' => 11500, 'tax_inclusive' => true]);

    $this->getJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}")
        ->assertOk()
        ->assertJsonPath('data.tenant.slug', $tenant->slug)
        ->assertJsonPath('data.product.name', '12-Week Transformation')
        ->assertJsonPath('data.product.prices.0.amount.amount_minor', 11500)
        ->assertJsonPath('data.tax_rate', 15)
        ->assertJsonPath('data.quotes.0.tax_minor', 1500)
        ->assertJsonPath('data.quotes.0.total_minor', 11500);

    $this->withHeader('Accept-Language', 'ar')->getJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}")
        ->assertJsonPath('data.product.name', 'برنامج التحول ١٢ أسبوعًا');
    $this->getJson("/api/v1/public/checkout/{$tenant->slug}/missing")->assertNotFound();
});

it('creates one order per idempotency key and exposes status polling', function () {
    [$tenant] = $this->coachIn('FR', 'EUR');
    $this->connectGateway($tenant, 'stripe');
    [$product, $price] = $this->makeProduct($tenant, [], ['amount_minor' => 10000, 'tax_inclusive' => false]);
    $payload = ['price_id' => $price->id, 'email' => 'claire@example.fr', 'name' => 'Claire'];

    $first = $this->withHeader('Idempotency-Key', 'key-1')->postJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}", $payload)->assertCreated();
    $again = $this->withHeader('Idempotency-Key', 'key-1')->postJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}", $payload)->assertCreated();
    expect($again->json('data.order_id'))->toBe($first->json('data.order_id'))
        ->and(Order::withoutGlobalScopes()->count())->toBe(1);

    $order = Order::withoutGlobalScopes()->first();
    expect($order->total_minor)->toBe(12000)->and($order->tax_minor)->toBe(2000)->and($order->tax_country)->toBe('FR');

    $this->getJson('/api/v1/public/orders/'.$order->id.'/status')->assertOk()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.fulfilled', false);
    $this->getJson('/api/v1/public/orders/01HZZZZZZZZZZZZZZZZZZZZZZZ/status')->assertNotFound();
});

it('applies coupons and validates them', function () {
    [$tenant] = $this->coachIn('AE', 'AED');
    $this->connectGateway($tenant, 'stripe');
    [$product, $price] = $this->makeProduct($tenant, [], ['amount_minor' => 21000, 'tax_inclusive' => true]);
    $this->inTenant($tenant, fn () => Coupon::create(['code' => 'RAMADAN20', 'type' => 'percent', 'value' => 20, 'max_redemptions' => 1]));

    $res = $this->postJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}", ['price_id' => $price->id, 'email' => 'a@example.com', 'name' => 'A', 'coupon' => 'ramadan20'])->assertCreated();
    $order = Order::withoutGlobalScopes()->find($res->json('data.order_id'));
    expect($order->discount_minor)->toBe(4200)->and($order->total_minor)->toBe(16800)->and($order->tax_minor)->toBe(800);

    expectApiError($this->postJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}", ['price_id' => $price->id, 'email' => 'b@example.com', 'name' => 'B', 'coupon' => 'NOPE']), 422, 'coupon_invalid');
});

it('settles 100%-off orders immediately without a gateway', function () {
    [$tenant] = $this->coachIn('QA', 'QAR');
    [$product, $price] = $this->makeProduct($tenant, ['fulfilment' => ['credits' => 2]], ['amount_minor' => 5000]);
    $this->inTenant($tenant, fn () => Coupon::create(['code' => 'FREE', 'type' => 'percent', 'value' => 100]));

    $res = $this->postJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}", ['price_id' => $price->id, 'email' => 'free@example.com', 'name' => 'Free', 'coupon' => 'FREE'])
        ->assertCreated()->assertJsonPath('data.status', 'paid')->assertJsonPath('data.checkout_url', null);
    $this->getJson('/api/v1/public/orders/'.$res->json('data.order_id').'/status')->assertJsonPath('data.fulfilled', true);
});

it('lists the shop for clients and hides iOS-hidden products on iOS', function () {
    [$tenant, $owner] = $this->coachIn('QA', 'QAR');
    $client = $this->addClient($tenant, $owner);
    $this->makeProduct($tenant, ['slug' => 'coaching', 'sell_in_app_ios' => 'external']);
    $this->makeProduct($tenant, ['slug' => 'ebook', 'type' => 'digital_program', 'sell_in_app_ios' => 'hidden']);
    $this->makeProduct($tenant, ['slug' => 'retired', 'is_active' => false]);

    $web = $this->actingAsIn($client, $tenant)->getJson('/api/v1/me/shop?platform=android')->assertOk();
    expect(collect($web->json('data'))->pluck('slug')->sort()->values()->all())->toBe(['coaching', 'ebook']);

    $ios = $this->actingAsIn($client, $tenant)->withHeader('User-Agent', 'Aurum/1.0 (iPhone; iOS 18.1)')->getJson('/api/v1/me/shop')->assertOk();
    expect(collect($ios->json('data'))->pluck('slug')->all())->toBe(['coaching'])
        ->and($ios->json('data.0.ios_purchase_mode'))->toBe('external');
});

it('lets a client check out in the app and blocks hidden products on iOS', function () {
    [$tenant, $owner] = $this->coachIn('FR', 'EUR');
    $this->connectGateway($tenant, 'stripe');
    $client = $this->addClient($tenant, $owner);
    [$product, $price] = $this->makeProduct($tenant);
    [$hidden, $hiddenPrice] = $this->makeProduct($tenant, ['sell_in_app_ios' => 'hidden']);

    $this->actingAsIn($client, $tenant)->withHeader('Idempotency-Key', 'app-1')->postJson("/api/v1/me/shop/{$product->id}/checkout", ['price_id' => $price->id])
        ->assertCreated()->assertJsonPath('data.checkout_url', 'https://checkout.stripe.com/c/pay/cs_X')->assertJsonPath('data.status', 'pending');
    expect(Order::withoutGlobalScopes()->latest()->first()->client_id)->toBe($client->id);

    expectApiError($this->actingAsIn($client, $tenant)->postJson("/api/v1/me/shop/{$hidden->id}/checkout", ['price_id' => $hiddenPrice->id, 'platform' => 'ios']), 403, 'forbidden');
});
