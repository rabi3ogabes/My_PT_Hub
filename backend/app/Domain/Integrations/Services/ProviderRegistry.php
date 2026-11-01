<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Integrations\Contracts\CalendarProvider;
use App\Domain\Integrations\Contracts\HealthProvider;
use App\Domain\Integrations\Contracts\OAuthProvider;
use App\Domain\Integrations\Contracts\VideoProvider;
use App\Domain\Shared\Support\ApiException;
use Illuminate\Contracts\Container\Container;

/** Resolves provider drivers and exposes the integration catalogue from config/integrations.php. */
final class ProviderRegistry
{
    /** @var array<string, OAuthProvider> */
    private array $resolved = [];

    public function __construct(private readonly Container $container) {}

    /** @return array<string, array<string, mixed>> */
    public function catalogue(): array
    {
        return (array) config('integrations.providers', []);
    }

    public function exists(string $provider): bool
    {
        return array_key_exists($provider, $this->catalogue());
    }

    /** @return array<string, mixed> */
    public function meta(string $provider): array
    {
        return $this->catalogue()[$provider] ?? throw new ApiException('not_found', __('integrations.errors.unknown_provider'), 404);
    }

    public function category(string $provider): string
    {
        return (string) ($this->meta($provider)['category'] ?? 'health');
    }

    public function method(string $provider): string
    {
        return (string) ($this->meta($provider)['method'] ?? 'oauth');
    }

    public function hasDriver(string $provider): bool
    {
        return isset($this->catalogue()[$provider]['driver']);
    }

    public function driver(string $provider): OAuthProvider
    {
        if (isset($this->resolved[$provider])) {
            return $this->resolved[$provider];
        }
        $class = $this->meta($provider)['driver'] ?? null;
        if (! $class) {
            throw new ApiException('integration_not_oauth', __('integrations.errors.not_oauth'), 422);
        }

        return $this->resolved[$provider] = $this->container->make($class);
    }

    public function health(string $provider): HealthProvider
    {
        $driver = $this->driver($provider);

        return $driver instanceof HealthProvider ? $driver : throw new ApiException('not_found', __('integrations.errors.unknown_provider'), 404);
    }

    public function calendar(string $provider): CalendarProvider
    {
        $driver = $this->driver($provider);

        return $driver instanceof CalendarProvider ? $driver : throw new ApiException('not_found', __('integrations.errors.unknown_provider'), 404);
    }

    public function video(string $provider): VideoProvider
    {
        $driver = $this->driver($provider);

        return $driver instanceof VideoProvider ? $driver : throw new ApiException('not_found', __('integrations.errors.unknown_provider'), 404);
    }

    /** @return list<string> */
    public function keysByCategory(string $category): array
    {
        return array_keys(array_filter($this->catalogue(), fn (array $m) => ($m['category'] ?? null) === $category));
    }

    public function isConfigured(string $provider): bool
    {
        $method = $this->method($provider);
        if ($method === 'device') {
            return true;
        }
        if ($method === 'optin') {
            return filled(config('services.whatsapp.token')) && filled(config('services.whatsapp.phone_number_id'));
        }

        return $this->driver($provider)->isConfigured();
    }

    /** Swaps a driver instance (used by tests and by custom deployments). */
    public function swap(string $provider, OAuthProvider $driver): void
    {
        $this->resolved[$provider] = $driver;
    }
}
