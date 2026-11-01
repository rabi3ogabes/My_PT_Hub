<?php

use App\Domain\Shared\Support\ErrorRenderer;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\IdempotencyKey;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [SetLocale::class]);
        $middleware->api(append: [SetLocale::class]);
        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'role' => EnsureRole::class,
            'idempotent' => IdempotencyKey::class,
            'active' => TrackActivity::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        ]);
        // The tenant must be resolved BEFORE route-model binding so that bound
        // models are filtered by the tenant scope (prevents cross-tenant access).
        $middleware->appendToPriorityList(\Illuminate\Auth\Middleware\Authenticate::class, ResolveTenant::class);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->validateCsrfTokens(except: ['webhooks/*', 'stripe/*']);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(fn (Throwable $e, Request $request) => ErrorRenderer::render($e, $request));
    })->create();
