<?php

use App\Domain\Payments\Gateways\Drivers\HyperPayGateway;
use App\Domain\Payments\Gateways\Drivers\MyFatoorahGateway;
use App\Domain\Payments\Gateways\Drivers\PaymobGateway;
use App\Domain\Payments\Gateways\Drivers\StripeGateway;
use App\Domain\Payments\Gateways\Drivers\TapGateway;
use App\Domain\Payments\Gateways\InvalidWebhookSignature;

it('verifies Stripe-Signature headers and rejects tampered or stale ones', function () {
    $payload = '{"id":"evt_1","type":"checkout.session.completed"}';
    $t = 1_800_000_000;
    $sig = hash_hmac('sha256', $t.'.'.$payload, 'whsec_x');

    StripeGateway::verifySignature($payload, "t={$t},v1={$sig}", 'whsec_x', $t + 10);
    expect(true)->toBeTrue();

    expect(fn () => StripeGateway::verifySignature($payload.' ', "t={$t},v1={$sig}", 'whsec_x', $t))->toThrow(InvalidWebhookSignature::class)
        ->and(fn () => StripeGateway::verifySignature($payload, "t={$t},v1={$sig}", 'whsec_other', $t))->toThrow(InvalidWebhookSignature::class)
        ->and(fn () => StripeGateway::verifySignature($payload, "t={$t},v1={$sig}", 'whsec_x', $t + 3600))->toThrow(InvalidWebhookSignature::class)
        ->and(fn () => StripeGateway::verifySignature($payload, '', 'whsec_x', $t))->toThrow(InvalidWebhookSignature::class);
});

it('builds the Tap hashstring with currency decimals', function () {
    $data = ['id' => 'chg_1', 'amount' => 12.5, 'currency' => 'KWD', 'status' => 'CAPTURED',
        'reference' => ['gateway' => 'g1', 'payment' => 'p1'], 'transaction' => ['created' => '1700000000000']];
    $expected = hash_hmac('sha256', 'x_idchg_1x_amount12.500x_currencyKWDx_gateway_referenceg1x_payment_referencep1x_statusCAPTUREDx_created1700000000000', 'sk');

    expect(TapGateway::hashString($data, 'sk'))->toBe($expected)
        ->and(TapGateway::hashString(['amount' => 13] + $data, 'sk'))->not->toBe($expected);
});

it('signs MyFatoorah webhook data sorted by key', function () {
    $data = ['TransactionStatus' => 'SUCCESS', 'InvoiceId' => 42, 'CustomerReference' => 'ord_1', 'PaymentId' => null];
    $expected = base64_encode(hash_hmac('sha256', 'CustomerReference=ord_1,InvoiceId=42,PaymentId=,TransactionStatus=SUCCESS', 'secret', true));

    expect(MyFatoorahGateway::signature($data, 'secret'))->toBe($expected);
});

it('computes the Paymob transaction HMAC (SHA-512) over the documented fields', function () {
    $obj = ['amount_cents' => 1000, 'created_at' => '2026-11-01T10:00:00', 'currency' => 'EGP', 'error_occured' => false, 'has_parent_transaction' => false,
        'id' => 99, 'integration_id' => 111, 'is_3d_secure' => true, 'is_auth' => false, 'is_capture' => false, 'is_refunded' => false,
        'is_standalone_payment' => true, 'is_voided' => false, 'order' => ['id' => 7], 'owner' => 5, 'pending' => false,
        'source_data' => ['pan' => '2346', 'sub_type' => 'MasterCard', 'type' => 'card'], 'success' => true];
    $concat = '10002026-11-01T10:00:00EGPfalsefalse99111truefalsefalsefalsetruefalse75false2346MasterCardcardtrue';

    expect(PaymobGateway::hmac($obj, 'k'))->toBe(hash_hmac('sha512', $concat, 'k'));
});

it('decrypts HyperPay AES-256-GCM notifications and rejects tampering', function () {
    $key = random_bytes(32);
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt('{"type":"PAYMENT"}', 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

    expect(HyperPayGateway::decrypt(bin2hex($cipher), bin2hex($iv), bin2hex($tag), bin2hex($key)))->toBe('{"type":"PAYMENT"}');

    $tampered = $cipher;
    $tampered[0] = chr(ord($tampered[0]) ^ 1);
    expect(fn () => HyperPayGateway::decrypt(bin2hex($tampered), bin2hex($iv), bin2hex($tag), bin2hex($key)))->toThrow(InvalidWebhookSignature::class)
        ->and(fn () => HyperPayGateway::decrypt(bin2hex($cipher), bin2hex($iv), bin2hex(random_bytes(16)), bin2hex($key)))->toThrow(InvalidWebhookSignature::class);
});
