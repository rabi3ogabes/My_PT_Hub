<?php

use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(function () {
    Notification::fake();
    [$this->tenant, $this->owner] = $this->coachIn('QA', 'QAR');
});

it('connects gateways with encrypted credentials that are never returned', function () {
    $res = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/payment-gateways', [
        'driver' => 'tap', 'label' => 'Tap Qatar', 'credentials' => ['secret_key' => 'sk_live_supersecret'], 'currencies' => ['qar', 'sar'], 'is_default' => true,
    ])->assertCreated()
        ->assertJsonPath('data.driver', 'tap')
        ->assertJsonPath('data.credentials_set', ['secret_key'])
        ->assertJsonPath('data.currencies', ['QAR', 'SAR'])
        ->assertJsonPath('data.is_default', true);
    expect($res->getContent())->not->toContain('sk_live_supersecret')
        ->and($res->json('data.webhook_url'))->toContain('/webhooks/payments/tap/');

    $gateway = PaymentGateway::withoutGlobalScopes()->findOrFail($res->json('data.id'));
    $raw = DB::table('payment_gateways')->where('id', $gateway->id)->value('credentials');
    expect($raw)->not->toContain('sk_live_supersecret')->and($gateway->credentials['secret_key'])->toBe('sk_live_supersecret');

    $index = $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/payment-gateways')->assertOk();
    expect($index->getContent())->not->toContain('sk_live_supersecret');

    // Partial credential update keeps existing keys; a second default for QAR demotes the first.
    $this->actingAsIn($this->owner, $this->tenant)->patchJson("/api/v1/payment-gateways/{$gateway->id}", ['label' => 'Tap'])->assertOk()->assertJsonPath('data.credentials_set', ['secret_key']);
    $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/payment-gateways', ['driver' => 'myfatoorah', 'credentials' => ['api_key' => 'k', 'webhook_secret' => 's'], 'currencies' => ['QAR'], 'is_default' => true])->assertCreated();
    expect($gateway->refresh()->is_default)->toBeFalse();

    $this->actingAsIn($this->owner, $this->tenant)->deleteJson("/api/v1/payment-gateways/{$gateway->id}")->assertOk()->assertJsonPath('data.deleted', true);
});

it('validates gateway credentials per driver and restricts management to payments.manage', function () {
    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/payment-gateways', ['driver' => 'paypal', 'credentials' => ['client_id' => 'x']]), 422, 'validation_failed');
    $coach = $this->addStaff($this->tenant, 'coach');
    expectApiError($this->actingAsIn($coach, $this->tenant)->postJson('/api/v1/payment-gateways', ['driver' => 'tap', 'credentials' => ['secret_key' => 'x']]), 403, 'forbidden');
});

it('manages products with nested prices, fulfilment config and iOS mode', function () {
    $res = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/products', [
        'type' => 'membership',
        'name' => ['en' => 'Online coaching', 'ar' => 'التدريب عن بُعد'],
        'description' => 'Weekly check-ins and a custom plan',
        'sell_in_app_ios' => 'external',
        'fulfilment' => ['credits' => 4, 'access_days' => 90, 'welcome_message' => 'Welcome {first_name}!'],
        'prices' => [
            ['type' => 'recurring', 'amount_minor' => 49900, 'currency' => 'qar', 'interval' => 'month', 'trial_days' => 7],
            ['type' => 'installments', 'amount_minor' => 50000, 'currency' => 'QAR', 'interval' => 'month', 'installments' => 3],
        ],
    ])->assertCreated()
        ->assertJsonPath('data.slug', 'online-coaching')
        ->assertJsonPath('data.name_translations.ar', 'التدريب عن بُعد')
        ->assertJsonPath('data.fulfilment.credits', 4)
        ->assertJsonPath('data.prices.0.amount.amount_minor', 49900)
        ->assertJsonPath('data.prices.0.trial_days', 7)
        ->assertJsonPath('data.prices.1.installments', 3)
        ->assertJsonPath('data.ios_purchase_mode', 'external');
    $id = $res->json('data.id');
    $keep = $res->json('data.prices.0.id');

    $this->actingAsIn($this->owner, $this->tenant)->patchJson("/api/v1/products/{$id}", [
        'prices' => [['id' => $keep, 'type' => 'recurring', 'amount_minor' => 59900, 'currency' => 'QAR', 'interval' => 'month']],
    ])->assertOk()->assertJsonPath('data.prices.0.amount.amount_minor', 59900);
    expect(Price::withoutGlobalScopes()->where('product_id', $id)->where('is_active', true)->count())->toBe(1);

    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/products', [
        'type' => 'coaching_package', 'name' => 'X', 'fulfilment' => ['meal_plan_id' => '01HZZZZZZZZZZZZZZZZZZZZZZZ'],
        'prices' => [['type' => 'one_time', 'amount_minor' => 100, 'currency' => 'QAR']],
    ]), 422, 'validation_failed');

    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/products?filter[type]=membership')->assertOk()->assertJsonPath('meta.total', 1);
    $this->actingAsIn($this->owner, $this->tenant)->deleteJson("/api/v1/products/{$id}")->assertOk();
    expect(Product::withoutGlobalScopes()->withTrashed()->find($id)->trashed())->toBeTrue();
});

it('returns a shareable payment link with a QR code', function () {
    [$product] = $this->makeProduct($this->tenant, ['slug' => 'summer-shred']);
    $this->actingAsIn($this->owner, $this->tenant)->getJson("/api/v1/products/{$product->id}/link")
        ->assertOk()
        ->assertJsonPath('data.url', url("checkout/{$this->tenant->slug}/summer-shred"))
        ->assertJson(fn ($json) => $json->where('data.qr_svg', fn ($v) => str_starts_with($v, 'data:image/svg+xml;base64,'))
            ->where('data.whatsapp_url', fn ($v) => str_starts_with($v, 'https://wa.me/?text='))->etc());
});

it('manages coupons with unique upper-case codes', function () {
    $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/coupons', ['code' => 'eid25', 'type' => 'percent', 'value' => 25, 'max_redemptions' => 50])
        ->assertCreated()->assertJsonPath('data.code', 'EID25');
    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/coupons', ['code' => 'EID25', 'type' => 'fixed', 'value' => 1000]), 422, 'validation_failed');
    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/coupons', ['code' => 'BIG', 'type' => 'percent', 'value' => 150]), 422, 'validation_failed');
    $fixed = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/coupons', ['code' => 'MINUS50', 'type' => 'fixed', 'value' => 5000])->assertCreated();
    expect($fixed->json('data.currency'))->toBe('QAR');
    $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/coupons')->assertOk()->assertJsonPath('meta.total', 2);
});
