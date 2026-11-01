<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['email' => 'required|email']);
        Password::sendResetLink(['email' => $this->email]);
        $this->sent = true; // same response whether the account exists or not
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->title(__('web.auth.forgot_title'));
    }
}
