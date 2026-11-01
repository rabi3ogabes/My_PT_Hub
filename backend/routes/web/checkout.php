<?php

use App\Http\Web\Marketing\CheckoutPageController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
| Public branded checkout for coach payment links ({APP_URL}/checkout/{tenant}/{product}).
| Session-free: the page talks to the public JSON API.
*/
Route::get('checkout/{tenantSlug}/{productSlug}', CheckoutPageController::class)
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
    ->where(['tenantSlug' => '[A-Za-z0-9-]+', 'productSlug' => '[A-Za-z0-9-]+'])
    ->name('checkout.show');
