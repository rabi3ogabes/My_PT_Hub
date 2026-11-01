<?php

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Models\Device;
use App\Domain\Identity\Models\MagicLink;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Notifications\MagicLinkNotification;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Mobile/API authentication.
 * Access tokens (ability "access") live 60 minutes; refresh tokens (ability "refresh")
 * live 60 days and are rotated on every use (old one revoked).
 */
final class AuthService
{
    public const ACCESS_TTL_MINUTES = 60;

    public const REFRESH_TTL_DAYS = 60;

    public const MAX_FAILED = 5;

    public const LOCK_MINUTES = 15;

    /**
     * @param  array{name: string, email: string, password?: ?string, locale?: ?string, timezone?: ?string, phone?: ?string}  $data
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'] ?? null,
            'phone' => $data['phone'] ?? null,
            'locale' => $data['locale'] ?? app()->getLocale(),
            'timezone' => $data['timezone'] ?? 'UTC',
        ]);
    }

    /** Validates credentials with account lockout. Returns the user (2FA still pending if enabled). */
    public function attempt(string $email, string $password): User
    {
        $user = User::query()->where('email', strtolower(trim($email)))->first();

        if ($user?->locked_until?->isFuture()) {
            throw new ApiException('account_locked', __('auth.locked', ['minutes' => (int) ceil(now()->diffInMinutes($user->locked_until))]), 423);
        }

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            if ($user) {
                $failed = $user->failed_logins + 1;
                $user->forceFill([
                    'failed_logins' => $failed >= self::MAX_FAILED ? 0 : $failed,
                    'locked_until' => $failed >= self::MAX_FAILED ? now()->addMinutes(self::LOCK_MINUTES) : null,
                ])->save();
                ActivityLogger::log('auth.failed', $user, causerId: $user->id, tenantId: $user->current_tenant_id);
            }
            throw new ApiException('invalid_credentials', __('auth.failed'), 401);
        }

        $user->forceFill(['failed_logins' => 0, 'locked_until' => null])->save();

        return $user;
    }

    /**
     * @param  array{device_name?: ?string, platform?: ?string, push_token?: ?string, app?: ?string, app_version?: ?string, timezone?: ?string}  $device
     * @return array<string, mixed>
     */
    public function issueTokens(User $user, array $device = []): array
    {
        $name = substr($device['device_name'] ?? 'api', 0, 120);
        $access = $user->createToken($name, ['access'], now()->addMinutes(self::ACCESS_TTL_MINUTES));
        $refresh = $user->createToken($name.':refresh', ['refresh'], now()->addDays(self::REFRESH_TTL_DAYS));

        if (! empty($device['platform'])) {
            $this->registerDevice($user, $device);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLogger::log('auth.login', $user, ['device' => $name], causerId: $user->id, tenantId: $user->current_tenant_id);

        return [
            'access_token' => $access->plainTextToken,
            'refresh_token' => $refresh->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $access->accessToken->expires_at?->toIso8601String(),
            'refresh_expires_at' => $refresh->accessToken->expires_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function refresh(string $refreshToken): array
    {
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($refreshToken);
        if (! $token || ! $token->can('refresh') || ($token->expires_at && $token->expires_at->isPast())) {
            throw new ApiException('invalid_refresh_token', __('auth.refresh_invalid'), 401);
        }
        /** @var User $user */
        $user = $token->tokenable;
        $deviceName = str_replace(':refresh', '', $token->name);

        return DB::transaction(function () use ($token, $user, $deviceName) {
            $token->delete();
            // Revoke expired access tokens for this device to keep the table lean.
            $user->tokens()->where('name', $deviceName)->where('expires_at', '<', now())->delete();

            return $this->issueTokens($user, ['device_name' => $deviceName]);
        });
    }

    public function logout(User $user, ?string $deviceName = null, ?string $pushToken = null): void
    {
        $current = $user->currentAccessToken();
        if ($current && method_exists($current, 'delete')) {
            $name = $current->name;
            $current->delete();
            $user->tokens()->where('name', $name.':refresh')->delete();
        }
        if ($pushToken) {
            $user->devices()->where('push_token', $pushToken)->delete();
        }
    }

    /**
     * @param  array<string, ?string>  $data
     */
    public function registerDevice(User $user, array $data): Device
    {
        $attributes = [
            'name' => $data['device_name'] ?? null,
            'platform' => $data['platform'] ?? 'web',
            'app' => $data['app'] ?? 'client',
            'app_version' => $data['app_version'] ?? null,
            'locale' => $data['locale'] ?? $user->locale,
            'timezone' => $data['timezone'] ?? $user->timezone,
            'last_seen_at' => now(),
        ];

        if (! empty($data['push_token'])) {
            // A push token belongs to one account at a time.
            Device::query()->where('push_token', $data['push_token'])->where('user_id', '!=', $user->id)->delete();

            return Device::query()->updateOrCreate(['user_id' => $user->id, 'push_token' => $data['push_token']], $attributes);
        }

        return $user->devices()->create($attributes);
    }

    // ---------------- Magic link ----------------

    public function sendMagicLink(string $email): void
    {
        $email = strtolower(trim($email));
        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return; // do not reveal whether the account exists
        }
        $plain = Str::random(48);
        MagicLink::create(['email' => $email, 'token_hash' => hash('sha256', $plain), 'expires_at' => now()->addMinutes(15)]);
        $user->notify(new MagicLinkNotification($plain));
    }

    public function consumeMagicLink(string $token): User
    {
        $link = MagicLink::query()->where('token_hash', hash('sha256', $token))->first();
        if (! $link || $link->used_at || $link->expires_at->isPast()) {
            throw new ApiException('magic_link_invalid', __('auth.magic_link_invalid'), 410);
        }
        $link->update(['used_at' => now()]);
        $user = User::query()->where('email', $link->email)->firstOrFail();
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }

    // ---------------- Social ----------------

    /** @return array{sub: string, email: ?string, name: ?string} */
    public function verifyGoogle(string $idToken): array
    {
        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
        $claims = $response->json() ?? [];
        $audiences = array_filter([config('services.google.client_id'), config('services.google.ios_client_id'), config('services.google.android_client_id')]);
        if ($response->failed() || ! in_array($claims['aud'] ?? null, $audiences, true) || ! in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new ApiException('social_token_invalid', __('auth.social_invalid'), 401);
        }

        return ['sub' => (string) $claims['sub'], 'email' => $claims['email'] ?? null, 'name' => $claims['name'] ?? null];
    }

    /** @return array{sub: string, email: ?string, name: ?string} */
    public function verifyApple(string $identityToken, ?string $name = null): array
    {
        $keys = Cache::remember('apple:jwks', 3600, fn () => Http::timeout(10)->get('https://appleid.apple.com/auth/keys')->throw()->json());
        try {
            $claims = (array) JWT::decode($identityToken, JWK::parseKeySet($keys));
        } catch (\Throwable) {
            throw new ApiException('social_token_invalid', __('auth.social_invalid'), 401);
        }
        $audiences = array_filter([config('services.apple.client_id'), ...config('services.apple.bundle_ids', [])]);
        if (($claims['iss'] ?? '') !== 'https://appleid.apple.com' || ! in_array($claims['aud'] ?? null, $audiences, true)) {
            throw new ApiException('social_token_invalid', __('auth.social_invalid'), 401);
        }

        return ['sub' => (string) $claims['sub'], 'email' => $claims['email'] ?? null, 'name' => $name];
    }

    /**
     * Finds or creates the user for a verified social identity.
     *
     * @param  array{sub: string, email: ?string, name: ?string}  $identity
     * @return array{0: User, 1: bool} [user, created]
     */
    public function socialUser(string $provider, array $identity): array
    {
        $column = $provider === 'apple' ? 'apple_id' : 'google_id';
        $user = User::query()->where($column, $identity['sub'])->first();
        if ($user) {
            return [$user, false];
        }
        if ($identity['email'] && ($user = User::query()->where('email', strtolower($identity['email']))->first())) {
            $user->forceFill([$column => $identity['sub'], 'email_verified_at' => $user->email_verified_at ?? now()])->save();

            return [$user, false];
        }
        if (! $identity['email']) {
            throw new ApiException('social_email_missing', __('auth.social_email_missing'), 422);
        }
        $user = User::create([
            'name' => $identity['name'] ?: Str::before($identity['email'], '@'),
            'email' => strtolower($identity['email']),
            'email_verified_at' => now(),
            $column => $identity['sub'],
            'locale' => app()->getLocale(),
        ]);

        return [$user, true];
    }

    // ---------------- Two-factor (TOTP) ----------------

    /** @return array{secret: string, otpauth_url: string} */
    public function beginTwoFactor(User $user): array
    {
        $g = new Google2FA;
        $secret = $g->generateSecretKey(32);
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => null])->save();

        return ['secret' => $secret, 'otpauth_url' => $g->getQRCodeUrl(config('brand.name'), $user->email, $secret)];
    }

    /** @return list<string> recovery codes */
    public function confirmTwoFactor(User $user, string $code): array
    {
        if (! $user->two_factor_secret || ! (new Google2FA)->verifyKey($user->two_factor_secret, $code, 1)) {
            throw ApiException::invalid('invalid_code', __('auth.two_factor_invalid'));
        }
        $codes = collect(range(1, 8))->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))->all();
        $user->forceFill(['two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => $codes])->save();
        ActivityLogger::log('auth.2fa_enabled', $user, causerId: $user->id);

        return $codes;
    }

    public function disableTwoFactor(User $user): void
    {
        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null, 'two_factor_recovery_codes' => null])->save();
        ActivityLogger::log('auth.2fa_disabled', $user, causerId: $user->id);
    }

    public function challengeFor(User $user): string
    {
        return Crypt::encryptString(json_encode(['uid' => $user->id, 'exp' => now()->addMinutes(5)->timestamp]));
    }

    public function verifyChallenge(string $challenge, string $code): User
    {
        try {
            $data = json_decode(Crypt::decryptString($challenge), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw new ApiException('challenge_invalid', __('auth.two_factor_expired'), 401);
        }
        if (($data['exp'] ?? 0) < time()) {
            throw new ApiException('challenge_invalid', __('auth.two_factor_expired'), 401);
        }
        $user = User::findOrFail($data['uid']);
        if (! $this->verifyCode($user, $code)) {
            throw new ApiException('invalid_code', __('auth.two_factor_invalid'), 401);
        }

        return $user;
    }

    public function verifyCode(User $user, string $code): bool
    {
        $code = trim($code);
        if ($user->two_factor_secret && (new Google2FA)->verifyKey($user->two_factor_secret, $code, 1)) {
            return true;
        }
        $codes = $user->two_factor_recovery_codes ?? [];
        if (in_array(strtoupper($code), $codes, true)) {
            $user->forceFill(['two_factor_recovery_codes' => array_values(array_diff($codes, [strtoupper($code)]))])->save();

            return true;
        }

        return false;
    }
}
