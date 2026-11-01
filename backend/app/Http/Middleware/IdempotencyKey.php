<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replays the stored response for a repeated Idempotency-Key (payment and
 * other non-idempotent POST endpoints). Keys are scoped per user and expire after 24h.
 */
class IdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');
        if (! $key || ! $request->isMethod('POST')) {
            return $next($request);
        }
        $cacheKey = 'idem:'.($request->user()?->id ?? $request->ip()).':'.hash('sha256', $key.'|'.$request->path());

        if ($stored = Cache::get($cacheKey)) {
            return response($stored['body'], $stored['status'], $stored['headers'] + ['Idempotent-Replay' => 'true']);
        }

        $lock = Cache::lock($cacheKey.':lock', 30);
        if (! $lock->get()) {
            return response()->json(['data' => null, 'meta' => (object) [], 'links' => (object) [], 'errors' => [['code' => 'idempotency_in_progress', 'message' => 'A request with this Idempotency-Key is in progress.', 'field_errors' => (object) []]]], 409);
        }

        try {
            $response = $next($request);
            if ($response->getStatusCode() < 500) {
                Cache::put($cacheKey, [
                    'body' => $response->getContent(),
                    'status' => $response->getStatusCode(),
                    'headers' => ['Content-Type' => $response->headers->get('Content-Type', 'application/json')],
                ], now()->addDay());
            }

            return $response;
        } finally {
            $lock->release();
        }
    }
}
