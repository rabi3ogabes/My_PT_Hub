<?php

namespace App\Http\Web\Auth;

use App\Domain\Identity\Services\AuthService;
use App\Domain\Tenancy\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

/** Web OAuth (Google; Apple via id_token in form_post). Coaches signing up get a workspace. */
class SocialiteController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        if ($provider === 'google') {
            return Socialite::driver('google')->redirectUrl(url(config('services.google.redirect')))->redirect();
        }
        $query = http_build_query([
            'response_type' => 'code id_token',
            'response_mode' => 'form_post',
            'client_id' => config('services.apple.client_id'),
            'redirect_uri' => route('social.callback', 'apple'),
            'scope' => 'name email',
            'state' => $state = bin2hex(random_bytes(16)),
        ]);
        session()->put('apple_state', $state);

        return redirect()->away('https://appleid.apple.com/auth/authorize?'.$query);
    }

    public function callback(string $provider, AuthService $auth, TenantProvisioner $provisioner): RedirectResponse
    {
        try {
            if ($provider === 'google') {
                $g = Socialite::driver('google')->redirectUrl(url(config('services.google.redirect')))->user();
                $identity = ['sub' => (string) $g->getId(), 'email' => $g->getEmail(), 'name' => $g->getName()];
            } else {
                abort_unless(hash_equals((string) session()->pull('apple_state'), (string) request('state')), 403);
                $name = json_decode((string) request('user'), true)['name'] ?? [];
                $identity = $auth->verifyApple((string) request('id_token'), trim(($name['firstName'] ?? '').' '.($name['lastName'] ?? '')) ?: null);
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors(['email' => __('auth.social_invalid')]);
        }

        $user = DB::transaction(function () use ($auth, $provisioner, $provider, $identity) {
            [$user, $created] = $auth->socialUser($provider, $identity);
            if ($created || $user->memberships()->count() === 0) {
                $provisioner->provision($user);
            }

            return $user;
        });
        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/app');
    }
}
