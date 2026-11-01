<?php

namespace App\Http\Api\V1\Identity;

use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;

/** In-app notification center (database channel of AppNotification). */
class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'filter.category' => ['nullable', 'string', 'max:32'],
            'filter.unread' => ['nullable', Rule::in(['0', '1', 'true', 'false'])],
        ]);
        $user = $this->user();

        $query = $user->notifications()->getQuery()
            ->reorder()
            ->when(in_array($request->input('filter.unread'), ['1', 'true'], true), fn ($q) => $q->whereNull('read_at'))
            ->orderByDesc('created_at')->orderByDesc('id');

        $category = $request->input('filter.category');
        $paginator = $category
            ? $query->where('data', 'like', '%"category":"'.str_replace(['%', '_', '"'], ['\%', '\_', ''], (string) $category).'"%')->cursorPaginate($this->perPage(30))
            : $query->cursorPaginate($this->perPage(30));

        $items = array_map(fn (DatabaseNotification $n) => $this->present($n), $paginator->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
            'links' => ['next' => $paginator->nextPageUrl(), 'prev' => $paginator->previousPageUrl()],
            'errors' => [],
        ]);
    }

    /** Marks the given notifications read, or all of them when `ids` is omitted. */
    public function markRead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['nullable', 'array', 'max:500'],
            'ids.*' => ['string', 'max:64'],
        ]);
        $user = $this->user();
        $query = $user->unreadNotifications()->getQuery();
        if (! empty($data['ids'])) {
            $query->whereIn('id', $data['ids']);
        }
        $marked = $query->update(['read_at' => now()]);

        return $this->ok(['marked' => $marked], ['unread_count' => $user->unreadNotifications()->count()]);
    }

    /** @return array<string, mixed> */
    private function present(DatabaseNotification $n): array
    {
        $data = is_array($n->data) ? $n->data : [];

        return [
            'id' => $n->id,
            'category' => $data['category'] ?? 'system',
            'title' => $data['title'] ?? '',
            'body' => $data['body'] ?? '',
            'link' => $data['link'] ?? null,
            'data' => (object) ($data['data'] ?? []),
            'read_at' => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->toIso8601String(),
        ];
    }
}
