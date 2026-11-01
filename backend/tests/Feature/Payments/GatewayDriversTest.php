<?php

use App\Domain\Payments\Gateways\Drivers\MyFatoorahGateway;
use App\Domain\Payments\Gateways\Drivers\PaymobGateway;
use App\Domain\Payments\Gateways\Drivers\TapGateway;
use App\Domain\Payments\Models\WebhookEvent;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(fn () => Notification::fake());

it('Stripe: opens a Checkout Session on the connected account and settles on a signed webhook', function () {
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_test_1', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1'])]);
    [, $gateway, $order, $response] = $this->openCheckout('stripe', 'FR', 'EUR');

    $response->assertJsonPath('data.checkout_url', 'https://checkout.stripe.com/c/pay/cs_test_1')->assertJsonPath('data.status', 'pending');
    Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://api.stripe.com/v1/checkout/sessions'
        && $r->hasHeader('Stripe-Account', 'acct_123')
        && $r['mode'] === 'payment' && $r['metadata[order_id]'] === $order->id
        && (int) $r['line_items[0][price_data][unit_amount]'] === 39900 && $r['line_items[0][price_data][currency]'] === 'eur');
    expect($order->refresh()->gateway_reference)->toBe('cs_test_1')->and($order->tax_minor)->toBe(6650);

    $payload = json_encode(['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => [
        'id' => 'cs_test_1', 'payment_status' => 'paid', 'payment_intent' => 'pi_1', 'amount_total' => 39900, 'currency' => 'eur',
        'metadata' => ['order_id' => $order->id], 'customer' => 'cus_1',
    ]]]);
    $t = time();
    $tampered = str_replace('39900', '100', $payload);
    $this->postWebhook($gateway, $tampered, ['Stripe-Signature' => "t={$t},v1=".hash_hmac('sha256', "{$t}.{$payload}", 'whsec_test_secret')])->assertStatus(400);
    expect($order->refresh()->status)->toBe('pending');

    $this->postWebhook($gateway, $payload, ['Stripe-Signature' => "t={$t},v1=".hash_hmac('sha256', "{$t}.{$payload}", 'whsec_test_secret')])->assertOk()->assertJsonPath('status', 'processed');
    $this->assertPaidAndFulfilled($order);
    expect($order->payments()->first()->gateway_payment_id)->toBe('pi_1');
});

it('PayPal: creates an Orders v2 order and only trusts webhooks PayPal verifies', function () {
    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'A21', 'expires_in' => 3600]),
        'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response(['id' => 'PPO1', 'status' => 'PAYER_ACTION_REQUIRED', 'links' => [['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PPO1']]], 201),
        'api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::sequence()
            ->push(['verification_status' => 'FAILURE'])
            ->push(['verification_status' => 'SUCCESS']),
    ]);
    [, $gateway, $order, $response] = $this->openCheckout('paypal', 'US', 'USD');
    $response->assertJsonPath('data.checkout_url', 'https://www.sandbox.paypal.com/checkoutnow?token=PPO1');
    Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/v2/checkout/orders') && $r->hasHeader('Authorization', 'Bearer A21')
        && $r['purchase_units'][0]['custom_id'] === $order->id && $r['purchase_units'][0]['amount']['value'] === '399.00');

    $body = json_encode(['id' => 'WH-EVT-1', 'event_type' => 'PAYMENT.CAPTURE.COMPLETED', 'resource' => [
        'id' => 'CAP1', 'status' => 'COMPLETED', 'custom_id' => $order->id, 'amount' => ['value' => '399.00', 'currency_code' => 'USD'],
        'supplementary_data' => ['related_ids' => ['order_id' => 'PPO1']],
    ]]);
    $headers = ['PAYPAL-AUTH-ALGO' => 'SHA256withRSA', 'PAYPAL-CERT-URL' => 'https://api.paypal.com/cert.pem', 'PAYPAL-TRANSMISSION-ID' => 't1',
        'PAYPAL-TRANSMISSION-SIG' => 'sig', 'PAYPAL-TRANSMISSION-TIME' => now()->toIso8601String()];

    $this->postWebhook($gateway, $body, $headers)->assertStatus(400);
    expect($order->refresh()->status)->toBe('pending');
    $this->postWebhook($gateway, $body, $headers)->assertOk();
    $this->assertPaidAndFulfilled($order);
    Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), 'verify-webhook-signature') && $r['webhook_id'] === 'WH-123' && $r['transmission_id'] === 't1');
});

it('Tap: creates a redirect charge and validates the hashstring', function () {
    Http::fake(['api.tap.company/v2/charges' => Http::response(['id' => 'chg_TS1', 'status' => 'INITIATED', 'transaction' => ['url' => 'https://checkout.tap.company/?mode=page&token=tok_1']])]);
    [, $gateway, $order, $response] = $this->openCheckout('tap', 'QA', 'QAR');
    $response->assertJsonPath('data.checkout_url', 'https://checkout.tap.company/?mode=page&token=tok_1');
    Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Bearer sk_test_tap') && $r['amount'] == 399 && $r['currency'] === 'QAR'
        && $r['source']['id'] === 'src_all' && $r['reference']['order'] === $order->id && str_contains($r['post']['url'], '/webhooks/payments/tap/'));

    $data = ['id' => 'chg_TS1', 'amount' => 399, 'currency' => 'QAR', 'status' => 'CAPTURED', 'reference' => ['gateway' => '123', 'payment' => '456', 'order' => $order->id],
        'transaction' => ['created' => '1730000000000'], 'source' => ['payment_method' => 'APPLE_PAY'], 'customer' => ['id' => 'cus_1'], 'card' => ['id' => 'card_1']];
    $hash = TapGateway::hashString($data, 'sk_test_tap');

    $this->postWebhook($gateway, json_encode(['amount' => 1] + $data), ['hashstring' => $hash])->assertStatus(400);
    $this->postWebhook($gateway, json_encode($data), ['hashstring' => $hash])->assertOk();
    $this->assertPaidAndFulfilled($order);
    expect($order->payments()->first()->method)->toBe('apple_pay');
});

it('PayTabs: requests a hosted payment page and verifies the HMAC signature header', function () {
    Http::fake(['secure.paytabs.sa/payment/request' => Http::response(['tran_ref' => 'TST2001', 'redirect_url' => 'https://secure.paytabs.sa/payment/page/ABC'])]);
    [, $gateway, $order, $response] = $this->openCheckout('paytabs', 'SA', 'SAR');
    $response->assertJsonPath('data.checkout_url', 'https://secure.paytabs.sa/payment/page/ABC');
    Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('authorization', 'SERVER-KEY-PT') && $r['profile_id'] === 12345 && $r['cart_id'] === $order->id && $r['cart_amount'] == 399);

    $body = json_encode(['tran_ref' => 'TST2001', 'tran_type' => 'Sale', 'cart_id' => $order->id, 'cart_currency' => 'SAR', 'cart_amount' => '399.00',
        'payment_result' => ['response_status' => 'A', 'response_message' => 'Authorised'], 'payment_info' => ['payment_method' => 'mada']]);
    $this->postWebhook($gateway, $body, ['signature' => hash_hmac('sha256', $body.' ', 'SERVER-KEY-PT')])->assertStatus(400);
    $this->postWebhook($gateway, $body, ['signature' => hash_hmac('sha256', $body, 'SERVER-KEY-PT')])->assertOk();
    $this->assertPaidAndFulfilled($order);
    expect($order->tax_minor)->toBe(5204); // KSA 15% inside SAR 399.00
});

it('HyperPay: prepares a COPYandPAY checkout and decrypts AES-GCM webhooks', function () {
    Http::fake(['eu-test.oppwa.com/v1/checkouts' => Http::response(['id' => 'CHK.1234', 'result' => ['code' => '000.200.100']])]);
    [, $gateway, $order, $response] = $this->openCheckout('hyperpay', 'SA', 'SAR');
    $response->assertJsonPath('data.checkout_url', url('pay/hyperpay/'.$order->id));
    Http::assertSent(fn (HttpRequest $r) => $r['entityId'] === 'hp_entity' && $r['amount'] === '399.00' && $r['paymentType'] === 'DB' && $r['merchantTransactionId'] === $order->id);
    $this->get('/pay/hyperpay/'.$order->id)->assertOk()->assertSee('paymentWidgets.js?checkoutId=CHK.1234', false);

    $plain = json_encode(['type' => 'PAYMENT', 'payload' => ['id' => '8ac7a4a1', 'paymentType' => 'DB', 'paymentBrand' => 'MADA', 'amount' => '399.00', 'currency' => 'SAR',
        'merchantTransactionId' => $order->id, 'result' => ['code' => '000.000.000', 'description' => 'Transaction succeeded']]]);
    $key = hex2bin($this->gatewayCredentials['hyperpay']['webhook_key']);
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

    $this->postWebhook($gateway, json_encode(['encryptedBody' => bin2hex($cipher)]), ['X-Initialization-Vector' => bin2hex($iv), 'X-Authentication-Tag' => bin2hex(random_bytes(16))])->assertStatus(400);
    $this->postWebhook($gateway, json_encode(['encryptedBody' => bin2hex($cipher)]), ['X-Initialization-Vector' => bin2hex($iv), 'X-Authentication-Tag' => bin2hex($tag)])->assertOk();
    $this->assertPaidAndFulfilled($order);
    expect($order->payments()->first()->method)->toBe('mada');
});

it('MyFatoorah: creates a payment link and verifies MyFatoorah-Signature', function () {
    Http::fake(['apitest.myfatoorah.com/v2/SendPayment' => Http::response(['IsSuccess' => true, 'Data' => ['InvoiceId' => 555, 'InvoiceURL' => 'https://demo.MyFatoorah.com/KWT/ie/0101'], 'Message' => ''])]);
    [, $gateway, $order, $response] = $this->openCheckout('myfatoorah', 'QA', 'QAR');
    $response->assertJsonPath('data.checkout_url', 'https://demo.MyFatoorah.com/KWT/ie/0101');
    Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Bearer mf_key') && $r['CustomerReference'] === $order->id && $r['InvoiceValue'] == 399 && $r['NotificationOption'] === 'LNK');

    $data = ['InvoiceId' => 555, 'InvoiceReference' => '2026000001', 'CustomerReference' => $order->id, 'UserDefinedField' => $order->id, 'TransactionStatus' => 'SUCCESS',
        'PaymentMethod' => 'KNET', 'PaymentId' => '0708', 'InvoiceValueInDisplayCurreny' => '399.000', 'DisplayCurrency' => 'QAR'];
    $sig = MyFatoorahGateway::signature($data, 'mf_secret');

    $this->postWebhook($gateway, json_encode(['EventType' => 1, 'Event' => 'TransactionsStatusChanged', 'Data' => ['TransactionStatus' => 'FAILED'] + $data]), ['MyFatoorah-Signature' => $sig])->assertStatus(400);
    $this->postWebhook($gateway, json_encode(['EventType' => 1, 'Event' => 'TransactionsStatusChanged', 'Data' => $data]), ['MyFatoorah-Signature' => $sig])->assertOk();
    $this->assertPaidAndFulfilled($order);
});

it('Paymob: creates a payment intention and verifies the HMAC query parameter', function () {
    Http::fake(['accept.paymob.com/v1/intention/' => Http::response(['id' => 'pi_test_1', 'client_secret' => 'egy_csk_test_1'], 201)]);
    [, $gateway, $order, $response] = $this->openCheckout('paymob', 'EG', 'EGP');
    expect($response->json('data.checkout_url'))->toBe('https://accept.paymob.com/unifiedcheckout/?publicKey=pm_public&clientSecret=egy_csk_test_1');
    Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Token pm_secret') && $r['amount'] === 39900 && $r['special_reference'] === $order->id && $r['payment_methods'] === [111]);

    $obj = ['id' => 777, 'amount_cents' => 39900, 'created_at' => '2026-11-01T10:00:00', 'currency' => 'EGP', 'error_occured' => false, 'has_parent_transaction' => false,
        'integration_id' => 111, 'is_3d_secure' => true, 'is_auth' => false, 'is_capture' => false, 'is_refunded' => false, 'is_standalone_payment' => true,
        'is_voided' => false, 'order' => ['id' => 9001, 'merchant_order_id' => $order->id], 'owner' => 5, 'pending' => false,
        'source_data' => ['pan' => '2346', 'sub_type' => 'MasterCard', 'type' => 'card'], 'success' => true];
    $hmac = PaymobGateway::hmac($obj, 'pm_hmac');

    $this->postWebhook($gateway, json_encode(['type' => 'TRANSACTION', 'obj' => ['amount_cents' => 100] + $obj]), [], '?hmac='.$hmac)->assertStatus(400);
    $this->postWebhook($gateway, json_encode(['type' => 'TRANSACTION', 'obj' => $obj]), [], '?hmac='.$hmac)->assertOk();
    $this->assertPaidAndFulfilled($order);
    expect($order->tax_minor)->toBe(4900); // Egypt 14% inside EGP 399.00
});

it('Flutterwave: creates a v3 payment and checks verif-hash then re-verifies the transaction', function () {
    Http::fake([
        'api.flutterwave.com/v3/payments' => Http::response(['status' => 'success', 'data' => ['link' => 'https://checkout.flutterwave.com/v3/hosted/pay/abc']]),
    ]);
    [, $gateway, $order, $response] = $this->openCheckout('flutterwave', 'NG', 'NGN');
    $response->assertJsonPath('data.checkout_url', 'https://checkout.flutterwave.com/v3/hosted/pay/abc');
    Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/v3/payments') && $r['tx_ref'] === $order->id && $r['amount'] === '399.00' && $r['currency'] === 'NGN');

    Http::fake(['api.flutterwave.com/v3/transactions/4455/verify' => Http::response(['status' => 'success', 'data' => [
        'id' => 4455, 'tx_ref' => $order->id, 'flw_ref' => 'FLW-1', 'status' => 'successful', 'amount' => 399, 'currency' => 'NGN', 'payment_type' => 'mobilemoney', 'customer' => ['email' => 'buyer@example.com'],
    ]])]);
    $body = json_encode(['event' => 'charge.completed', 'data' => ['id' => 4455, 'tx_ref' => $order->id, 'status' => 'successful', 'amount' => 399, 'currency' => 'NGN']]);
    $this->postWebhook($gateway, $body, ['verif-hash' => 'wrong'])->assertStatus(400);
    $this->postWebhook($gateway, $body, ['verif-hash' => 'flw_hash'])->assertOk();
    $this->assertPaidAndFulfilled($order);
});

it('Manual: returns no checkout URL and leaves the order awaiting a recorded payment', function () {
    [, $gateway, $order, $response] = $this->openCheckout('manual', 'MA', 'MAD');
    $response->assertJsonPath('data.checkout_url', null);
    expect($order->refresh()->status)->toBe('pending')->and($order->tax_minor)->toBe(6650);
    $this->postWebhook($gateway, '{}')->assertStatus(404);
});

it('rejects webhook amounts lower than the order total', function () {
    Http::fake(['api.tap.company/v2/charges' => Http::response(['id' => 'chg_LOW', 'transaction' => ['url' => 'https://tap.test/pay']])]);
    [, $gateway, $order] = $this->openCheckout('tap', 'QA', 'QAR');
    $data = ['id' => 'chg_LOW', 'amount' => 1, 'currency' => 'QAR', 'status' => 'CAPTURED', 'reference' => ['gateway' => 'g', 'payment' => 'p', 'order' => $order->id], 'transaction' => ['created' => '1']];

    $this->postWebhook($gateway, json_encode($data), ['hashstring' => TapGateway::hashString($data, 'sk_test_tap')])->assertOk();
    expect($order->refresh()->status)->toBe('pending')->and($order->payments()->where('status', 'failed')->value('failure_reason'))->toBe('amount_mismatch');
});

it('records every processed event once in webhook_events', function () {
    Http::fake(['api.tap.company/v2/charges' => Http::response(['id' => 'chg_E', 'transaction' => ['url' => 'https://tap.test/pay']])]);
    [, $gateway, $order] = $this->openCheckout('tap', 'QA', 'QAR');
    $data = ['id' => 'chg_E', 'amount' => 399, 'currency' => 'QAR', 'status' => 'CAPTURED', 'reference' => ['gateway' => 'g', 'payment' => 'p', 'order' => $order->id], 'transaction' => ['created' => '1']];
    $headers = ['hashstring' => TapGateway::hashString($data, 'sk_test_tap')];

    $this->postWebhook($gateway, json_encode($data), $headers)->assertJsonPath('status', 'processed');
    $this->postWebhook($gateway, json_encode($data), $headers)->assertJsonPath('status', 'duplicate');
    expect(WebhookEvent::query()->where('driver', 'tap')->count())->toBe(1)
        ->and(WebhookEvent::query()->first()->status)->toBe('processed')
        ->and(WebhookEvent::query()->first()->tenant_id)->toBe($gateway->tenant_id);
});
