<?php

namespace App\Http\Web\Auth;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Shared\Support\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    public function consume(string $token, AuthService $auth): RedirectResponse
    {
        try {
            $user = $auth->consumeMagicLink($token);
        } catch (ApiException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        }
        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/app');
    }
}
