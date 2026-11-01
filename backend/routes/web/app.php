<?php

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\WebTenant;
use App\Livewire\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Coach dashboard (web). Tenant context comes from WebTenant; clients are sent
| to the "Get the app" page.
*/

Route::get('locale/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);
    $request->session()->put('locale', $locale);
    $request->user()?->forceFill(['locale' => $locale])->save();
    $back = url()->previous();

    return redirect()->to(str_starts_with($back, url('/')) ? $back : url('/app'));
})->name('locale.switch');

Route::middleware(['auth', WebTenant::class])->prefix('app')->name('app.')->group(function () {
    Route::get('/', App\Dashboard::class)->name('dashboard');
    Route::get('onboarding', App\Onboarding::class)->name('onboarding');
    Route::get('get-the-app', App\GetTheApp::class)->name('get-the-app');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', fn () => redirect()->route('app.settings.profile'))->name('index');
        Route::get('profile', App\Settings\Profile::class)->name('profile');
        Route::get('business', App\Settings\Business::class)->name('business');
        Route::get('team', App\Settings\Team::class)->name('team');
        Route::get('billing', App\Settings\Billing::class)->name('billing');
        Route::get('notifications', App\Settings\Notifications::class)->name('notifications');
    });
});
