<?php

use App\Http\Web\Payments\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
| Inbound webhooks (CSRF excluded for webhooks/*). Signatures are verified per provider.
*/
Route::post('webhooks/payments/{driver}/{gateway}', [PaymentWebhookController::class, 'gateway'])
    ->where(['driver' => '[a-z]+', 'gateway' => '[0-9A-Za-z]{26}'])
    ->middleware('throttle:600,1')
    ->name('webhooks.payments');

Route::post('webhooks/stripe/saas', [PaymentWebhookController::class, 'stripeSaas'])
    ->middleware('throttle:600,1')
    ->name('webhooks.stripe.saas');
