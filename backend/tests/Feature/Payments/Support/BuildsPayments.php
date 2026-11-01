<?php

namespace Tests\Feature\Payments\Support;

use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Services\CheckoutService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Str;

/** Fixtures shared by the Phase 5 payment tests. */
trait BuildsPayments
{
    /** @var array<string, array<string, mixed>> */
    protected array $gatewayCredentials = [
        'stripe' => ['account_id' => 'acct_123', 'webhook_secret' => 'whsec_test_secret'],
        'paypal' => ['client_id' => 'pp_client', 'client_secret' => 'pp_secret', 'webhook_id' => 'WH-123'],
        'tap' => ['secret_key' => 'sk_test_tap'],
        'paytabs' => ['profile_id' => '12345', 'server_key' => 'SERVER-KEY-PT', 'region' => 'SAU'],
        'hyperpay' => ['access_token' => 'hp_token', 'entity_id' => 'hp_entity', 'webhook_key' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f'],
        'myfatoorah' => ['api_key' => 'mf_key', 'webhook_secret' => 'mf_secret', 'country' => 'KWT'],
        'paymob' => ['secret_key' => 'pm_secret', 'public_key' => 'pm_public', 'hmac_secret' => 'pm_hmac', 'integration_ids' => [111], 'region' => 'egypt'],
        'flutterwave' => ['secret_key' => 'FLWSECK_TEST', 'secret_hash' => 'flw_hash'],
        'manual' => [],
    ];

    public function connectGateway(Tenant $tenant, string $driver, array $attributes = []): PaymentGateway
    {
        return $this->inTenant($tenant, fn () => PaymentGateway::create(array_merge([
            'driver' => $driver,
            'label' => ucfirst($driver),
            'credentials' => $this->gatewayCredentials[$driver],
            'is_default' => true,
            'is_live' => false,
            'is_active' => true,
        ], $attributes)));
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<string, mixed>  $price
     * @return array{0: Product, 1: Price}
     */
    public function makeProduct(Tenant $tenant, array $product = [], array $price = []): array
    {
        return $this->inTenant($tenant, function () use ($tenant, $product, $price) {
            $p = Product::create(array_merge([
                'type' => 'coaching_package',
                'slug' => 'package-'.Str::lower(Str::random(6)),
                'name' => ['en' => '12-Week Transformation', 'ar' => 'برنامج التحول ١٢ أسبوعًا'],
                'sell_in_app_ios' => 'external',
                'is_active' => true,
            ], $product));
            $pr = Price::create(array_merge([
                'product_id' => $p->id,
                'type' => 'one_time',
                'amount_minor' => 39900,
                'currency' => $tenant->currency,
                'tax_inclusive' => true,
                'is_active' => true,
            ], $price));

            return [$p, $pr];
        });
    }

    public function makeOrder(Tenant $tenant, Product $product, Price $price, array $customer = ['email' => 'buyer@example.com', 'name' => 'Omar Buyer']): Order
    {
        return app(CheckoutService::class)->createOrder($tenant, $product, $price, $customer);
    }

    /** Coach tenant with a currency/country (and the context cleared). */
    public function coachIn(string $country, string $currency): array
    {
        return $this->createCoachTenant([], ['country' => $country, 'currency' => $currency]);
    }

    /**
     * Creates a tenant + gateway + product and opens a public checkout.
     *
     * @return array{0: Tenant, 1: PaymentGateway, 2: Order, 3: \Illuminate\Testing\TestResponse}
     */
    public function openCheckout(string $driver, string $country, string $currency, array $price = []): array
    {
        [$tenant] = $this->coachIn($country, $currency);
        $gateway = $this->connectGateway($tenant, $driver);
        [$product, $p] = $this->makeProduct($tenant, ['fulfilment' => ['credits' => 4]], $price);
        $response = $this->postJson("/api/v1/public/checkout/{$tenant->slug}/{$product->slug}", [
            'price_id' => $p->id, 'email' => 'buyer@example.com', 'name' => 'Omar Buyer',
        ]);
        $response->assertCreated();
        $order = Order::withoutGlobalScopes()->findOrFail($response->json('data.order_id'));

        return [$tenant, $gateway, $order, $response];
    }

    public function assertPaidAndFulfilled(Order $order, int $credits = 4): void
    {
        $order->refresh();
        expect($order->status)->toBe('paid')
            ->and($order->fulfilled_at)->not->toBeNull()
            ->and($order->client_id)->not->toBeNull()
            ->and((int) \App\Domain\Calendar\Models\CreditLedgerEntry::withoutGlobalScopes()->where('order_id', $order->id)->sum('amount'))->toBe($credits)
            ->and(\App\Domain\Payments\Models\Invoice::withoutGlobalScopes()->where('order_id', $order->id)->count())->toBe(1);
    }

    /** @param array<string, mixed> $attributes */
    public function makeSubscription(Tenant $tenant, Product $product, Price $price, array $attributes = []): \App\Domain\Payments\Models\ClientSubscription
    {
        $client = $this->addClient($tenant);

        return $this->inTenant($tenant, fn () => \App\Domain\Payments\Models\ClientSubscription::create(array_merge([
            'client_id' => $client->id, 'product_id' => $product->id, 'price_id' => $price->id, 'status' => 'active',
            'amount_minor' => 30000, 'currency' => $tenant->currency, 'interval' => 'month', 'interval_count' => 1,
            'current_period_start' => now()->subDays(5), 'current_period_end' => now()->addDays(25),
        ], $attributes)));
    }

    public function postWebhook(PaymentGateway $gateway, string $body, array $headers = [], string $query = ''): \Illuminate\Testing\TestResponse
    {
        $server = [];
        foreach ($headers as $k => $v) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $k))] = $v;
        }
        $server['CONTENT_TYPE'] = $headers['Content-Type'] ?? 'application/json';

        return $this->call('POST', '/webhooks/payments/'.$gateway->driver.'/'.$gateway->id.$query, [], [], [], $server, $body);
    }
}
