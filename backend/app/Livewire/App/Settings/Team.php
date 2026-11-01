<?php

namespace App\Livewire\App\Settings;

use App\Domain\Billing\Services\PlanGate;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Services\InvitationService;
use App\Domain\Tenancy\Services\MembershipService;
use App\Livewire\App\Concerns\WithTenant;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Staff members: invite coaches/assistants, change roles, archive — within plan limits. */
#[Layout('layouts.app')]
class Team extends Component
{
    use WithTenant;

    public bool $inviting = false;

    public string $inviteName = '';

    public string $inviteEmail = '';

    public string $inviteRole = 'coach';

    public ?string $archivingId = null;

    public bool $confirmingArchive = false;

    public function openInvite(): void
    {
        abort_unless($this->isOwner(), 403);
        $this->resetValidation();
        $this->inviting = true;
    }

    public function invite(InvitationService $invitations): void
    {
        abort_unless($this->isOwner(), 403);
        $data = $this->validate([
            'inviteName' => ['nullable', 'string', 'max:120'],
            'inviteEmail' => ['required', 'email:rfc', 'max:190'],
            'inviteRole' => ['required', Rule::in(['coach', 'assistant'])],
        ]);
        try {
            $invitations->create($this->tenant(), $this->user(), ['email' => $data['inviteEmail'], 'name' => $data['inviteName'] ?: null, 'role' => $data['inviteRole']]);
        } catch (ApiException $e) {
            $this->addError('inviteEmail', $e->getMessage());

            return;
        }
        $this->reset('inviteName', 'inviteEmail', 'inviting');
        $this->inviteRole = 'coach';
        $this->toast(__('web.team.invited'));
    }

    public function changeRole(string $userId, string $role, MembershipService $memberships): void
    {
        abort_unless($this->isOwner(), 403);
        abort_unless(in_array($role, ['owner', 'coach', 'assistant'], true), 422);
        $member = $this->member($userId);
        try {
            $memberships->changeRole($this->tenant(), $member, $role);
        } catch (ApiException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }
        $this->toast(__('web.team.role_changed', ['name' => $member->name]));
    }

    public function confirmArchive(string $userId): void
    {
        abort_unless($this->isOwner(), 403);
        $this->archivingId = $this->member($userId)->id;
        $this->confirmingArchive = true;
    }

    public function archive(MembershipService $memberships): void
    {
        abort_unless($this->isOwner() && $this->archivingId, 403);
        $member = $this->member($this->archivingId);
        if ($member->id === $this->user()->id) {
            $this->toast(__('web.team.cannot_archive_self'), 'error');
            $this->reset('archivingId', 'confirmingArchive');

            return;
        }
        $memberships->archive($this->tenant(), $member);
        $this->reset('archivingId', 'confirmingArchive');
        $this->toast(__('web.team.archived', ['name' => $member->name]));
    }

    public function revoke(string $invitationId): void
    {
        abort_unless($this->isOwner(), 403);
        Invitation::query()->whereKey($invitationId)->whereNull('accepted_at')->delete();
        $this->toast(__('web.team.revoked'));
    }

    /** Staff member of the current tenant (never another tenant's user). */
    private function member(string $userId): User
    {
        $membership = Membership::query()->where('user_id', $userId)->whereIn('role', ['owner', 'coach', 'assistant'])->firstOrFail();

        return User::query()->findOrFail($membership->user_id);
    }

    public function render(PlanGate $gate)
    {
        $tenant = $this->tenant();
        $members = Membership::query()->whereIn('role', ['owner', 'coach', 'assistant'])->where('status', 'active')
            ->with('user:id,name,email,avatar_path,last_active_at')->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'coach' THEN 1 ELSE 2 END")->get();
        $pending = Invitation::query()->whereIn('role', ['coach', 'assistant'])->whereNull('accepted_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->latest()->get();
        $limit = $gate->limit($tenant, 'coaches');
        $used = $tenant->staffCount();

        return view('livewire.app.settings.team', [
            'members' => $members,
            'pending' => $pending,
            'limit' => $limit,
            'used' => $used,
            'canInvite' => $limit === null || $used < $limit,
            'canManage' => $this->isOwner(),
            'archiving' => $this->archivingId ? $members->firstWhere('user_id', $this->archivingId)?->user : null,
        ])->title(__('web.settings.team'));
    }
}
