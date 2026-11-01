<?php

namespace App\Livewire\Auth;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Tenancy\Services\TenantProvisioner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** One-step coach trial sign-up (no card). */
#[Layout('layouts.auth')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $terms = false;

    public string $timezone = 'UTC';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    public function register(AuthService $auth, TenantProvisioner $provisioner): mixed
    {
        $data = $this->validate();
        $user = DB::transaction(function () use ($auth, $provisioner, $data) {
            $user = $auth->register($data + ['locale' => app()->getLocale()]);
            $provisioner->provision($user, ['timezone' => $data['timezone'] ?: 'UTC', 'locale' => app()->getLocale()]);

            return $user;
        });
        event(new Registered($user));
        Auth::login($user, true);
        session()->regenerate();

        return $this->redirect('/app/onboarding', navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.register')->title(__('web.auth.start_trial'));
    }
}
