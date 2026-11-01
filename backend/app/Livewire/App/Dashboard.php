<?php

namespace App\Livewire\App;

use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Services\InvitationService;
use App\Livewire\App\Concerns\WithTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Coach home: greeting, onboarding checklist, quick invite, lazy KPI panel. */
#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithTenant;

    public bool $inviting = false;

    public string $inviteName = '';

    public string $inviteEmail = '';

    public function openInvite(): void
    {
        $this->resetValidation();
        $this->inviting = true;
    }

    public function invite(InvitationService $invitations): void
    {
        $data = $this->validate([
            'inviteName' => ['nullable', 'string', 'max:120'],
            'inviteEmail' => ['required', 'email:rfc', 'max:190'],
        ]);
        try {
            $invitations->create($this->tenant(), $this->user(), ['email' => $data['inviteEmail'], 'name' => $data['inviteName'] ?: null, 'role' => 'client']);
        } catch (ApiException $e) {
            $this->addError('inviteEmail', $e->getMessage());

            return;
        }
        $this->reset('inviteName', 'inviteEmail', 'inviting');
        $this->toast(__('web.dashboard.invite_sent'));
    }

    /** @return list<array{key: string, done: bool, href: ?string, action: ?string}> */
    public function checklist(): array
    {
        $t = $this->tenant()->id;
        $uid = $this->user()->id;
        $link = fn (array $names) => ($n = collect($names)->first(fn ($r) => Route::has($r))) ? route($n) : null;

        return [
            ['key' => 'program', 'done' => DB::table('programs')->where('tenant_id', $t)->whereNull('deleted_at')->exists(), 'href' => $link(['app.programs.create', 'app.programs.index', 'app.programs']), 'action' => null],
            ['key' => 'invite', 'done' => DB::table('invitations')->where('tenant_id', $t)->exists() || DB::table('tenant_user')->where('tenant_id', $t)->where('role', 'client')->exists(), 'href' => null, 'action' => 'openInvite'],
            ['key' => 'payments', 'done' => DB::table('payment_gateways')->where('tenant_id', $t)->where('is_active', true)->exists(), 'href' => $link(['app.payments.settings', 'app.payments.index', 'app.payments']), 'action' => null],
            ['key' => 'profile', 'done' => DB::table('coach_profiles')->where('tenant_id', $t)->where('user_id', $uid)->where('is_published', true)->exists(), 'href' => route('app.settings.profile').'#public-profile', 'action' => null],
        ];
    }

    public function render()
    {
        $checklist = $this->checklist();

        return view('livewire.app.dashboard', [
            'checklist' => $checklist,
            'progress' => (int) round(collect($checklist)->where('done', true)->count() / count($checklist) * 100),
            'greeting' => match (true) {
                now($this->user()->timezone ?: 'UTC')->hour < 12 => 'morning',
                now($this->user()->timezone ?: 'UTC')->hour < 18 => 'afternoon',
                default => 'evening',
            },
        ])->title(__('web.nav.dashboard'));
    }
}
