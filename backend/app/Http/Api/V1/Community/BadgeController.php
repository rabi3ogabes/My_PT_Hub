<?php

namespace App\Http\Api\V1\Community;

use App\Domain\Community\Models\UserBadge;
use App\Domain\Community\Services\BadgeService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Membership;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Badge cabinet: the catalog with earned state; staff may view a client's cabinet with ?user_id=. */
class BadgeController extends ApiController
{
    public function __construct(private readonly BadgeService $badges) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['user_id' => ['nullable', 'string', 'size:26']]);
        $userId = $this->user()->id;
        if (! empty($data['user_id']) && $data['user_id'] !== $userId) {
            if (! $this->user()->isStaffIn() || ! Membership::query()->where('user_id', $data['user_id'])->exists()) {
                throw ApiException::forbidden(__('community_module.errors.badges_forbidden'));
            }
            $userId = $data['user_id'];
        }

        $earned = UserBadge::query()->where('user_id', $userId)->get()->keyBy('badge_key');
        $keys = array_values(array_unique([...BadgeService::BADGES, ...$earned->keys()->all()]));

        $items = array_map(fn (string $key) => [
            'key' => $key,
            'name' => $this->badges->label($key),
            'description' => $this->badges->description($key),
            'earned' => $earned->has($key),
            'awarded_at' => $earned->get($key)?->awarded_at?->toIso8601String(),
        ], $keys);

        return $this->ok($items, ['earned_count' => $earned->count()]);
    }
}
