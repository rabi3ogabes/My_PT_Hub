<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class VerifyEmailNotice extends Component
{
    public bool $resent = false;

    public function resend(): void
    {
        $user = auth()->user();
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
        $this->resent = true;
    }

    public function render()
    {
        return view('livewire.auth.verify-email')->title(__('web.auth.verify_title'));
    }
}
