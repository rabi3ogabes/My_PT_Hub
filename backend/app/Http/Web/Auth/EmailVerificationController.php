<?php

namespace App\Http\Web\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

/** Works without an active session (link opened on another device, e.g. from the mobile app). */
class EmailVerificationController extends Controller
{
    public function verify(string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->to(auth()->check() ? '/app' : '/login')->with('status', __('web.auth.verified'));
    }
}
