<?php

namespace App\Livewire\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(): mixed
    {
        $data = $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);
        $status = Password::reset($data + ['password_confirmation' => $this->password_confirmation], function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'failed_logins' => 0, 'locked_until' => null])->save();
            $user->tokens()->delete();
        });
        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return null;
        }
        session()->flash('status', __($status));

        return $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.reset-password')->title(__('web.auth.reset_title'));
    }
}
