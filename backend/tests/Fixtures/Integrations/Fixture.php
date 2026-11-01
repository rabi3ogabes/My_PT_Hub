<?php

namespace Tests\Fixtures\Integrations;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Models\Integration;

/** Recorded provider responses (VCR-style) and helpers for integration tests. */
final class Fixture
{
    public static function raw(string $name): string
    {
        return (string) file_get_contents(__DIR__.'/'.$name.'.json');
    }

    /** @return array<mixed> */
    public static function json(string $name): array
    {
        return json_decode(self::raw($name), true, flags: JSON_THROW_ON_ERROR);
    }

    /** Fills provider credentials so every driver reports itself as configured. */
    public static function configureProviders(): void
    {
        foreach (['garmin' => ['key' => 'garmin-key', 'secret' => 'garmin-secret']] + array_fill_keys(['fitbit', 'strava', 'oura', 'whoop', 'withings', 'polar', 'google_calendar', 'microsoft', 'zoom'], null) as $service => $creds) {
            config(["services.{$service}" => array_merge((array) config("services.{$service}"), $creds ?? ['client_id' => $service.'-client', 'client_secret' => $service.'-secret'])]);
        }
        config([
            'services.fitbit.verify' => 'fitbit-verify-code',
            'services.strava.verify_token' => 'strava-verify',
            'services.whatsapp' => ['token' => 'wa-token', 'phone_number_id' => '106540352242922', 'verify_token' => 'wa-verify', 'app_secret' => 'wa-app-secret'],
            'integrations.providers.oura.verification_token' => 'oura-verify',
            'integrations.providers.polar.webhook_secret' => 'polar-secret',
            'integrations.providers.garmin.webhook_token' => 'garmin-hook-token',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    public static function integration(User $user, string $provider, array $attributes = []): Integration
    {
        return Integration::create(array_merge([
            'user_id' => $user->id,
            'provider' => $provider,
            'status' => 'connected',
            'access_token' => $provider.'-access',
            'refresh_token' => $provider.'-refresh',
            'token_expires_at' => now()->addHours(5),
            'external_user_id' => $provider.'-user',
            'settings' => ['timezone' => 'UTC'],
        ], $attributes));
    }
}
