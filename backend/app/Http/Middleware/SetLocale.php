<?php

namespace App\Http\Middleware;

use App\Domain\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Locale resolution: route {locale} → user preference → Accept-Language → tenant default → app default. */
class SetLocale
{
    public const SUPPORTED = ['en', 'ar', 'fr', 'es'];

    public const RTL = ['ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);
        app()->setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function resolve(Request $request): string
    {
        $candidates = [
            $request->route('locale'),
            $request->query('lang'),
            $request->hasSession() ? $request->session()->get('locale') : null,
            $request->user()?->locale,
            $request->getPreferredLanguage(self::SUPPORTED),
            app(TenantContext::class)->get()?->locale,
        ];
        foreach ($candidates as $c) {
            if (is_string($c) && in_array($c, self::SUPPORTED, true)) {
                return $c;
            }
        }

        return config('app.locale', 'en');
    }

    public static function isRtl(?string $locale = null): bool
    {
        return in_array($locale ?? app()->getLocale(), self::RTL, true);
    }
}
