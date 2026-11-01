<?php

namespace App\Domain\Shared\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/** Renders every API failure as {data:null, meta:{}, links:{}, errors:[{code,message,field_errors}]}. */
final class ErrorRenderer
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! ($request->is('api/*') || $request->expectsJson())) {
            return null;
        }

        [$status, $code, $message, $fields] = match (true) {
            $e instanceof ApiException => [$e->status, $e->errorCode, $e->getMessage(), $e->fieldErrors],
            $e instanceof ValidationException => [422, 'validation_failed', __('api.validation_failed'), $e->errors()],
            $e instanceof AuthenticationException => [401, 'unauthenticated', __('api.unauthenticated'), []],
            $e instanceof AuthorizationException => [403, 'forbidden', $e->getMessage() ?: __('api.forbidden'), []],
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => [404, 'not_found', __('api.not_found'), []],
            $e instanceof ThrottleRequestsException => [429, 'too_many_requests', __('api.too_many_requests'), []],
            $e instanceof HttpExceptionInterface => [$e->getStatusCode(), 'http_error', $e->getMessage() ?: 'Error', []],
            default => [500, 'server_error', config('app.debug') ? $e->getMessage() : __('api.server_error'), []],
        };

        $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

        return response()->json([
            'data' => null,
            'meta' => (object) [],
            'links' => (object) [],
            'errors' => [[
                'code' => $code,
                'message' => $message,
                'field_errors' => (object) $fields,
            ]],
        ], $status, $headers);
    }
}
