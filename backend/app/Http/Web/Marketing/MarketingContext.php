<?php

namespace App\Http\Web\Marketing;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public site middleware: makes {locale} the default for route() and adds
 * CDN-friendly caching headers to successful, session-free GET responses.
 */
class MarketingContext
{
    public function handle(Request $request, Closure $next, string $maxAge = '600'): Response
    {
        if ($locale = $request->route('locale')) {
            URL::defaults(['locale' => $locale]);
        }

        $response = $next($request);

        if ($request->routeIs('marketing.templates.download')) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif ($request->isMethod('GET') && $response->getStatusCode() === 200 && ! $response->headers->has('Set-Cookie')) {
            $response->headers->set('Cache-Control', 'public, max-age='.(int) $maxAge.', s-maxage='.((int) $maxAge * 6).', stale-while-revalidate=86400');
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        return $response;
    }
}
