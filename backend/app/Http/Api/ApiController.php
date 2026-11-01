<?php

namespace App\Http\Api;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;

/**
 * Base API controller. Every response uses the envelope
 * {"data": ..., "meta": {...}, "links": {...}, "errors": []}.
 */
abstract class ApiController extends Controller
{
    protected function user(): User
    {
        /** @var User $user */
        $user = request()->user();

        return $user;
    }

    protected function tenant(): Tenant
    {
        return app(TenantContext::class)->require();
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function ok(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        return response()->json([
            'data' => $data,
            'meta' => (object) $meta,
            'links' => (object) [],
            'errors' => [],
        ], $status);
    }

    protected function created(mixed $data = null): JsonResponse
    {
        return $this->ok($data, [], 201);
    }

    protected function noContent(): JsonResponse
    {
        return $this->ok(null, [], 200);
    }

    /**
     * @param  class-string<JsonResource>  $resource
     * @param  array<string, mixed>  $meta
     */
    protected function paginated(CursorPaginator|LengthAwarePaginator $paginator, string $resource, array $meta = []): JsonResponse
    {
        $items = $resource::collection($paginator->items())->resolve(request());

        if ($paginator instanceof CursorPaginator) {
            $meta += ['per_page' => $paginator->perPage(), 'next_cursor' => $paginator->nextCursor()?->encode(), 'prev_cursor' => $paginator->previousCursor()?->encode()];
            $links = ['next' => $paginator->nextPageUrl(), 'prev' => $paginator->previousPageUrl()];
        } else {
            $meta += ['per_page' => $paginator->perPage(), 'current_page' => $paginator->currentPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()];
            $links = ['next' => $paginator->nextPageUrl(), 'prev' => $paginator->previousPageUrl()];
        }

        return response()->json(['data' => $items, 'meta' => $meta, 'links' => $links, 'errors' => []]);
    }

    protected function perPage(int $default = 25, int $max = 100): int
    {
        return max(1, min($max, (int) request()->integer('per_page', $default)));
    }
}
