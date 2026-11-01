<?php

namespace App\Livewire\Auth;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\InvitationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Invite landing page. Clients are pushed to the app (with deep link + store badges);
 * they can also create the account on the web. Staff invitations finish on the web.
 */
#[Layout('layouts.auth')]
class AcceptInvitation extends Component
{
    public string $code = '';

    public ?string $error = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $terms = false;

    public bool $done = false;

    public function mount(string $code, InvitationService $invitations): void
    {
        $this->code = strtoupper($code);
        try {
            $inv = $invitations->findUsable($this->code);
            $this->email = (string) $inv->email;
            $this->name = (string) $inv->name;
        } catch (ApiException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function accept(AuthService $auth, InvitationService $invitations): mixed
    {
        if (Auth::check()) {
            $invitations->accept($this->code, Auth::user());

            return $this->redirect('/app');
        }
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ]);
        try {
            $user = DB::transaction(function () use ($auth, $invitations, $data) {
                $user = $auth->register($data);
                $invitations->accept($this->code, $user);

                return $user;
            });
        } catch (ApiException $e) {
            $this->addError('email', $e->getMessage());

            return null;
        }
        Auth::login($user, true);
        $this->done = true;
        $invitation = Invitation::withoutGlobalScopes()->where('code', $this->code)->first();

        return $invitation?->role === 'client' ? null : $this->redirect('/app');
    }

    public function render()
    {
        $invitation = $this->error ? null : Invitation::withoutGlobalScopes()->with('inviter')->where('code', $this->code)->first();

        return view('livewire.auth.accept-invitation', [
            'invitation' => $invitation,
            'tenant' => $invitation ? Tenant::find($invitation->tenant_id) : null,
        ])->title(__('web.auth.invite_title'));
    }
}
