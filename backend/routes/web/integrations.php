<?php

use App\Http\Api\V1\Integrations\IntegrationController;
use App\Http\Web\Integrations\ProviderWebhookController;
use App\Http\Web\Marketplace\ReviewSubmissionController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
| Integrations (Phase 7): OAuth callbacks and inbound provider webhooks.
| Webhook paths live under webhooks/* (CSRF-exempt); each driver verifies
| its own signature / verification token.
*/
Route::get('integrations/{provider}/callback', [IntegrationController::class, 'callback'])
    ->where('provider', '[a-z_]+')->middleware('throttle:60,1')->name('integrations.callback');

Route::get('webhooks/integrations/{provider}', [ProviderWebhookController::class, 'verify'])
    ->where('provider', '[a-z_]+')->middleware('throttle:120,1')->name('integrations.webhook.verify');
Route::post('webhooks/integrations/{provider}', [ProviderWebhookController::class, 'handle'])
    ->where('provider', '[a-z_]+')->middleware('throttle:1200,1')->name('integrations.webhook');

// Marketplace (Phase 8 §4): review submission through the signed link sent 30 days after joining.
Route::post('marketplace/reviews/{tenant}/{client}', ReviewSubmissionController::class)
    ->middleware(['signed', 'throttle:10,1'])->withoutMiddleware(ValidateCsrfToken::class)->name('marketplace.reviews.submit');
