<?php

use App\Http\Web\Payments\PaymentReturnController;
use Illuminate\Support\Facades\Route;

/*
| Shopper-facing pages around hosted checkouts (client → coach payments).
*/
Route::middleware('throttle:60,1')->group(function () {
    Route::get('pay/return/{order}', [PaymentReturnController::class, 'show'])->where('order', '[0-9A-Za-z]{26}')->name('payments.return');
    Route::get('pay/hyperpay/{order}', [PaymentReturnController::class, 'hyperpay'])->where('order', '[0-9A-Za-z]{26}')->name('payments.hyperpay');
});
