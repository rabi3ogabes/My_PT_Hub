<?php

use App\Http\Web\Marketing as M;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
| Public marketing site (Phase 8). Every page lives under /{locale}. Pages run
| without the session stack so responses carry no cookies and can be cached by
| a CDN (Cache-Control: public). Lead forms are anonymous, throttled and use a
| honeypot instead of CSRF (there is no session to forge).
*/

$sessionless = [StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class];

Route::withoutMiddleware($sessionless)->group(function () {
    Route::get('/', M\LocaleRedirectController::class)->name('marketing.root');
    Route::get('robots.txt', [M\SeoController::class, 'robots'])->name('marketing.robots');
    Route::get('sitemap.xml', [M\SeoController::class, 'index'])->name('marketing.sitemap');
    Route::get('sitemaps/{locale}/{type}.xml', [M\SeoController::class, 'sitemap'])
        ->whereIn('locale', ['en', 'ar', 'fr', 'es'])->whereIn('type', M\SeoController::TYPES)->name('marketing.sitemap.show');
    Route::get('.well-known/apple-app-site-association', [M\SeoController::class, 'appleAppSiteAssociation'])->name('marketing.aasa');
    Route::get('apple-app-site-association', [M\SeoController::class, 'appleAppSiteAssociation']);
    Route::get('.well-known/assetlinks.json', [M\SeoController::class, 'assetLinks'])->name('marketing.assetlinks');
    Route::get('brand/logo.svg', [M\SeoController::class, 'logo'])->name('marketing.logo');
    Route::get('brand/og.png', [M\SeoController::class, 'ogImage'])->name('marketing.og');

    Route::prefix('{locale}')
        ->whereIn('locale', ['en', 'ar', 'fr', 'es'])
        ->middleware(M\MarketingContext::class)
        ->name('marketing.')
        ->group(function () {
            Route::get('/', [M\PageController::class, 'home'])->name('home');
            Route::get('features', [M\PageController::class, 'features'])->name('features.index');
            Route::get('features/{module}', [M\PageController::class, 'feature'])->name('features.show');
            Route::get('solutions/{segment}', [M\PageController::class, 'solution'])->name('solutions.show');
            Route::get('pricing', [M\PageController::class, 'pricing'])->name('pricing');
            Route::get('compare/{competitor}', [M\PageController::class, 'compare'])->name('compare');
            Route::get('switch', [M\PageController::class, 'switch'])->name('switch');
            Route::get('about', [M\PageController::class, 'about'])->name('about');
            Route::get('contact', [M\PageController::class, 'contact'])->name('contact');
            Route::post('contact', [M\PageController::class, 'contactSubmit'])->middleware('throttle:6,1')->name('contact.submit');
            Route::get('legal/{page}', [M\PageController::class, 'legal'])->name('legal');

            Route::get('calculators', [M\PageController::class, 'calculators'])->name('calculators.index');
            Route::get('calculators/{calculator}', [M\PageController::class, 'calculator'])->name('calculators.show');

            Route::get('exercises', [M\ExerciseController::class, 'index'])->name('exercises.index');
            Route::get('exercises/{slug}', [M\ExerciseController::class, 'show'])->name('exercises.show');

            Route::get('templates', [M\TemplateController::class, 'index'])->name('templates.index');
            Route::get('templates/{slug}', [M\TemplateController::class, 'show'])->name('templates.show');
            Route::post('templates/{slug}', [M\TemplateController::class, 'lead'])->middleware('throttle:10,1')->name('templates.lead');
            Route::get('templates/{slug}/download', [M\TemplateController::class, 'download'])->middleware('signed')->name('templates.download');

            Route::get('coaches', [M\CoachController::class, 'index'])->name('coaches.index');
            Route::get('coaches/city/{city}', [M\CoachController::class, 'city'])->where('city', '[a-z0-9-]+')->name('coaches.city');
            Route::get('coaches/{slug}', [M\CoachController::class, 'show'])->name('coaches.show');

            Route::get('blog', [M\BlogController::class, 'index'])->name('blog.index');
            Route::get('blog/{slug}', [M\BlogController::class, 'show'])->name('blog.show');
        });
});
