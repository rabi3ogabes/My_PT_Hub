<?php

namespace App\Livewire\Auth;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Shared\Support\ApiException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = true;

    public bool $magicSent = false;

    public function login(AuthService $auth): mixed
    {
        $this->validate();
        $key = 'web-login:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('email', __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]));

            return null;
        }
        RateLimiter::hit($key, 60);

        try {
            $user = $auth->attempt($this->email, $this->password);
        } catch (ApiException $e) {
            $this->addError('email', $e->getMessage());

            return null;
        }

        if ($user->hasTwoFactorEnabled()) {
            session()->put('two_factor_challenge', $auth->challengeFor($user));
            session()->put('two_factor_remember', $this->remember);

            return $this->redirectRoute('two-factor.challenge', navigate: true);
        }

        Auth::login($user, $this->remember);
        session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return $this->redirectIntended($user->is_super_admin && ! $user->current_tenant_id ? '/admin' : '/app', navigate: false);
    }

    public function sendMagicLink(AuthService $auth): void
    {
        $this->validateOnly('email');
        $auth->sendMagicLink($this->email);
        $this->magicSent = true;
    }

    public function render()
    {
        return view('livewire.auth.login')->title(__('web.auth.sign_in'));
    }
}
