<?php

namespace App\Http\Api\V1\Community;

use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Services\CommunityService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientGroup;
use App\Domain\Workouts\Models\Program;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Community\SpaceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceController extends ApiController
{
    public function __construct(private readonly CommunityService $community) {}

    public function index(Request $request): JsonResponse
    {
        $spaces = $this->community->visibleSpaces(CommunitySpace::query(), $this->user())
            ->withCount(['posts' => fn ($q) => $q->where('is_hidden', false)])
            ->orderBy('created_at')->get();

        return $this->ok(SpaceResource::collection($spaces)->resolve($request));
    }

    public function store(Request $request): JsonResponse
    {
        $this->assertModerator();
        $space = CommunitySpace::create($this->validated($request, true));

        return $this->created(new SpaceResource($space));
    }

    public function update(Request $request, CommunitySpace $space): JsonResponse
    {
        $this->assertModerator();
        $space->update($this->validated($request, false));

        return $this->ok(new SpaceResource($space));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'client_group_id' => ['nullable', 'string', 'size:26'],
            'program_id' => ['nullable', 'string', 'size:26'],
            'announcement_only' => ['nullable', 'boolean'],
        ]);
        if (! empty($data['client_group_id']) && ! ClientGroup::query()->whereKey($data['client_group_id'])->exists()) {
            throw ApiException::invalid('validation_failed', __('community_module.errors.group_not_found'), ['client_group_id' => [__('community_module.errors.group_not_found')]]);
        }
        if (! empty($data['program_id']) && ! Program::query()->whereKey($data['program_id'])->exists()) {
            throw ApiException::invalid('validation_failed', __('community_module.errors.program_not_found'), ['program_id' => [__('community_module.errors.program_not_found')]]);
        }
        if (array_key_exists('announcement_only', $data)) {
            $data['announcement_only'] = (bool) $data['announcement_only'];
        }

        return $data;
    }

    private function assertModerator(): void
    {
        if (! $this->community->isModerator($this->user())) {
            throw ApiException::forbidden(__('community_module.errors.moderators_only'));
        }
    }
}
