<?php

namespace App\Livewire\Auth;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Shared\Support\ApiException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class TwoFactorChallenge extends Component
{
    public string $code = '';

    public function mount(): void
    {
        if (! session()->has('two_factor_challenge')) {
            $this->redirectRoute('login');
        }
    }

    public function verify(AuthService $auth): mixed
    {
        $this->validate(['code' => 'required|string|max:16']);
        try {
            $user = $auth->verifyChallenge((string) session('two_factor_challenge'), $this->code);
        } catch (ApiException $e) {
            $this->addError('code', $e->getMessage());

            return null;
        }
        Auth::login($user, (bool) session()->pull('two_factor_remember', false));
        session()->forget('two_factor_challenge');
        session()->regenerate();

        return $this->redirectIntended('/app');
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge')->title(__('web.auth.two_factor_title'));
    }
}
