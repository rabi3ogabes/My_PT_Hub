<?php

use App\Http\Web\Auth\EmailVerificationController;
use App\Http\Web\Auth\SocialiteController;
use App\Livewire\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', Auth\Login::class)->name('login');
    Route::get('register', Auth\Register::class)->name('register');
    Route::get('forgot-password', Auth\ForgotPassword::class)->name('password.request');
    Route::get('reset-password/{token}', Auth\ResetPassword::class)->name('password.reset');
    Route::get('auth/magic/{token}', [App\Http\Web\Auth\MagicLinkController::class, 'consume'])->name('magic.consume');
    Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->whereIn('provider', ['google', 'apple'])->name('social.redirect');
    Route::match(['get', 'post'], 'auth/{provider}/callback', [SocialiteController::class, 'callback'])->whereIn('provider', ['google', 'apple'])->name('social.callback');
    Route::get('two-factor', Auth\TwoFactorChallenge::class)->name('two-factor.challenge');
});

Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::get('invite/{code}', Auth\AcceptInvitation::class)->name('invite.show');

Route::middleware('auth')->group(function () {
    Route::get('email/verify', Auth\VerifyEmailNotice::class)->name('verification.notice');
    Route::post('logout', [App\Http\Web\Auth\LogoutController::class, '__invoke'])->name('logout');
});
