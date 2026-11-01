<?php

namespace App\Http\Api\V1;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\AuthService;
use App\Domain\Tenancy\Services\InvitationService;
use App\Domain\Tenancy\Services\TenantProvisioner;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\TenantResource;
use App\Http\Api\V1\Resources\UserResource;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends ApiController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly TenantProvisioner $provisioner,
        private readonly InvitationService $invitations,
    ) {}

    /** @return array<string, mixed> */
    private function deviceRules(): array
    {
        return [
            'device_name' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', Rule::in(['ios', 'android', 'web'])],
            'push_token' => ['nullable', 'string', 'max:512'],
            'app' => ['nullable', Rule::in(['client', 'coach'])],
            'app_version' => ['nullable', 'string', 'max:32'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    /**
     * Register as coach (creates a business), client (via invite_code) or standalone client.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', PasswordRule::min(8)->letters()->numbers()],
            'account_type' => ['required', Rule::in(['coach', 'client', 'standalone'])],
            'invite_code' => ['required_if:account_type,client', 'nullable', 'string', 'max:32'],
            'business_name' => ['nullable', 'string', 'max:120'],
            'currency' => ['nullable', 'string', 'size:3'],
            'country' => ['nullable', 'string', 'size:2'],
            'locale' => ['nullable', Rule::in(['en', 'ar', 'fr', 'es'])],
            'terms' => ['accepted'],
            ...$this->deviceRules(),
        ]);

        if ($data['account_type'] === 'client') {
            $this->invitations->findUsable($data['invite_code']); // fail fast before creating the user
        }

        $user = DB::transaction(function () use ($data) {
            $user = $this->auth->register($data);
            match ($data['account_type']) {
                'coach' => $this->provisioner->provision($user, array_filter([
                    'name' => $data['business_name'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'country' => $data['country'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'locale' => $data['locale'] ?? null,
                ])),
                'client' => $this->invitations->accept($data['invite_code'], $user),
                'standalone' => $this->provisioner->provision($user, ['type' => 'personal', 'timezone' => $data['timezone'] ?? null, 'locale' => $data['locale'] ?? null]),
            };

            return $user->refresh();
        });

        event(new Registered($user));

        return $this->created($this->session($user, $this->auth->issueTokens($user, $data)));
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            ...$this->deviceRules(),
        ]);

        $user = $this->auth->attempt($data['email'], $data['password']);

        if ($user->hasTwoFactorEnabled()) {
            return $this->ok(['two_factor_required' => true, 'challenge' => $this->auth->challengeFor($user)]);
        }

        return $this->ok($this->session($user, $this->auth->issueTokens($user, $data)));
    }

    public function twoFactorChallenge(Request $request): JsonResponse
    {
        $data = $request->validate(['challenge' => ['required', 'string'], 'code' => ['required', 'string', 'max:16'], ...$this->deviceRules()]);
        $user = $this->auth->verifyChallenge($data['challenge'], $data['code']);

        return $this->ok($this->session($user, $this->auth->issueTokens($user, $data)));
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate(['refresh_token' => ['required', 'string']]);

        return $this->ok($this->auth->refresh($data['refresh_token']));
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($this->user(), pushToken: $request->input('push_token'));

        return $this->noContent();
    }

    public function magicLink(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $this->auth->sendMagicLink($data['email']);

        return $this->ok(['sent' => true]);
    }

    public function magicLinkVerify(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string'], ...$this->deviceRules()]);
        $user = $this->auth->consumeMagicLink($data['token']);

        return $this->ok($this->session($user, $this->auth->issueTokens($user, $data)));
    }

    public function social(Request $request, string $provider): JsonResponse
    {
        abort_unless(in_array($provider, ['google', 'apple'], true), 404);
        $data = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:120'],
            'account_type' => ['nullable', Rule::in(['coach', 'client', 'standalone'])],
            'invite_code' => ['nullable', 'string', 'max:32'],
            ...$this->deviceRules(),
        ]);
        $identity = $provider === 'google' ? $this->auth->verifyGoogle($data['token']) : $this->auth->verifyApple($data['token'], $data['name'] ?? null);

        $user = DB::transaction(function () use ($provider, $identity, $data) {
            [$user, $created] = $this->auth->socialUser($provider, $identity);
            if (! empty($data['invite_code'])) {
                $this->invitations->accept($data['invite_code'], $user);
            } elseif ($created || $user->memberships()->count() === 0) {
                $type = $data['account_type'] ?? 'standalone';
                $this->provisioner->provision($user, $type === 'coach' ? [] : ['type' => 'personal']);
            }

            return $user->refresh();
        });

        return $this->ok($this->session($user, $this->auth->issueTokens($user, $data)));
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($request->only('email'));

        return $this->ok(['sent' => true]); // never reveal existence
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'failed_logins' => 0, 'locked_until' => null])->save();
            $user->tokens()->delete();
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw new \App\Domain\Shared\Support\ApiException('reset_failed', __($status), 422);
        }

        return $this->ok(['reset' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok($this->profile($this->user()));
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = $this->user();
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'locale' => ['sometimes', Rule::in(['en', 'ar', 'fr', 'es'])],
            'timezone' => ['sometimes', 'timezone:all'],
            'units' => ['sometimes', Rule::in(['metric', 'imperial'])],
            'theme' => ['sometimes', Rule::in(['light', 'dark', 'system'])],
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.*.push' => ['boolean'],
            'notification_preferences.*.email' => ['boolean'],
            'quiet_hours' => ['sometimes', 'array'],
            'quiet_hours.enabled' => ['boolean'],
            'quiet_hours.from' => ['nullable', 'date_format:H:i'],
            'quiet_hours.to' => ['nullable', 'date_format:H:i'],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
            'password' => ['sometimes', PasswordRule::min(8)->letters()->numbers()],
        ]);
        unset($data['current_password']);
        $user->fill($data)->save();

        return $this->ok($this->profile($user->refresh()));
    }

    public function registerDevice(Request $request): JsonResponse
    {
        $data = $request->validate([...$this->deviceRules(), 'platform' => ['required', Rule::in(['ios', 'android', 'web'])]]);
        $device = $this->auth->registerDevice($this->user(), $data);

        return $this->ok(['id' => $device->id]);
    }

    public function twoFactorEnable(): JsonResponse
    {
        return $this->ok($this->auth->beginTwoFactor($this->user()));
    }

    public function twoFactorConfirm(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        return $this->ok(['recovery_codes' => $this->auth->confirmTwoFactor($this->user(), $data['code'])]);
    }

    public function twoFactorDisable(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'current_password:sanctum']]);
        $this->auth->disableTwoFactor($this->user());

        return $this->noContent();
    }

    public function acceptInvitation(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $this->invitations->accept($data['code'], $this->user());

        return $this->ok($this->profile($this->user()->refresh()));
    }

    public function invitationPreview(string $code): JsonResponse
    {
        $invitation = $this->invitations->findUsable($code);
        $tenant = \App\Domain\Tenancy\Models\Tenant::findOrFail($invitation->tenant_id);

        return $this->ok([
            'code' => $invitation->code,
            'role' => $invitation->role,
            'email' => $invitation->email,
            'name' => $invitation->name,
            'tenant' => ['name' => $tenant->name, 'logo_url' => (new TenantResource($tenant))->resolve()['logo_url'], 'brand_color' => $tenant->brand_color],
            'coach' => $invitation->inviter?->only(['name']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $tokens
     * @return array<string, mixed>
     */
    private function session(User $user, array $tokens): array
    {
        return array_merge($tokens, $this->profile($user));
    }

    /** @return array<string, mixed> */
    private function profile(User $user): array
    {
        $tenants = $user->tenants()->wherePivot('status', 'active')->get();

        return [
            'user' => (new UserResource($user))->resolve(),
            'tenants' => TenantResource::collection($tenants)->resolve(),
        ];
    }
}
