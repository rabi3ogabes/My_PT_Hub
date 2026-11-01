<?php

namespace App\Http\Api\V1\Tenancy;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Services\InvitationService;
use App\Domain\Tenancy\Services\MembershipService;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Coach\ClientController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeamController extends ApiController
{
    public function __construct(private readonly MembershipService $memberships, private readonly InvitationService $invitations) {}

    public function index(): JsonResponse
    {
        $members = Membership::query()->with('user')->whereIn('role', ['owner', 'coach', 'assistant'])->orderBy('created_at')->get();

        return $this->ok($members->map(fn (Membership $m) => [
            'id' => $m->user_id,
            'name' => $m->user?->name,
            'email' => $m->user?->email,
            'avatar_url' => $m->user?->avatar_path ? Storage::disk('public')->url($m->user->avatar_path) : null,
            'role' => $m->role,
            'status' => $m->status,
            'clients_count' => \App\Domain\Tenancy\Models\ClientProfile::query()->where('coach_id', $m->user_id)->where('status', 'active')->count(),
            'joined_at' => $m->joined_at?->toIso8601String(),
        ]));
    }

    public function invite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in(['coach', 'assistant'])],
        ]);

        return $this->created(ClientController::invitationPayload($this->invitations->create($this->tenant(), $this->user(), $data)));
    }

    public function update(Request $request, string $user): JsonResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(['owner', 'coach', 'assistant'])]]);
        $target = User::findOrFail($user);
        abort_unless($target->isStaffIn($this->tenant()->id), 404);
        $this->memberships->changeRole($this->tenant(), $target, $data['role']);

        return $this->index();
    }

    public function destroy(string $user): JsonResponse
    {
        $target = User::findOrFail($user);
        abort_unless($target->isStaffIn($this->tenant()->id), 404);
        if ($target->isOwnerIn($this->tenant()->id) && $this->tenant()->memberships()->where('role', 'owner')->where('status', 'active')->count() <= 1) {
            throw new \App\Domain\Shared\Support\ApiException('last_owner', __('api.last_owner'));
        }
        $this->memberships->archive($this->tenant(), $target);

        return $this->noContent();
    }
}
