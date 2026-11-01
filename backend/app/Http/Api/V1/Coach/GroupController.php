<?php

namespace App\Http\Api\V1\Coach;

use App\Domain\Tenancy\Models\ClientGroup;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends ApiController
{
    private function payload(ClientGroup $g): array
    {
        return ['id' => $g->id, 'name' => $g->name, 'color' => $g->color, 'members_count' => $g->members_count ?? $g->members()->count(), 'member_ids' => $g->relationLoaded('members') ? $g->members->pluck('id') : null];
    }

    public function index(): JsonResponse
    {
        return $this->ok(ClientGroup::query()->withCount('members')->orderBy('name')->get()->map(fn ($g) => $this->payload($g)));
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $tenantId = $this->tenant()->id;

        return [
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:80'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'member_ids' => ['sometimes', 'array'],
            'member_ids.*' => ['string', Rule::exists('tenant_user', 'user_id')->where('tenant_id', $tenantId)->where('role', 'client')],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));
        $group = ClientGroup::create(['name' => $data['name'], 'color' => $data['color'] ?? null]);
        $group->members()->sync($data['member_ids'] ?? []);

        return $this->created($this->payload($group->load('members')));
    }

    public function show(ClientGroup $group): JsonResponse
    {
        return $this->ok($this->payload($group->load('members')));
    }

    public function update(Request $request, ClientGroup $group): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        $group->fill(collect($data)->only(['name', 'color'])->all())->save();
        if (array_key_exists('member_ids', $data)) {
            $group->members()->sync($data['member_ids']);
        }

        return $this->ok($this->payload($group->load('members')));
    }

    public function destroy(ClientGroup $group): JsonResponse
    {
        $group->delete();

        return $this->noContent();
    }
}
