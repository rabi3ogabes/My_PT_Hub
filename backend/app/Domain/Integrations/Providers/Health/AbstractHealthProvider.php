<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Contracts\HealthProvider;
use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Providers\OAuth2Provider;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractHealthProvider extends OAuth2Provider implements HealthProvider
{
    public function backfill(Integration $integration, int $days): array
    {
        return $this->pull($integration, CarbonImmutable::now()->subDays($days)->startOfDay());
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        return null;
    }

    /**
     * Default: re-pull the window the notification references (or the last two days).
     */
    public function fetchNotification(Integration $integration, WebhookNotification $notification): array
    {
        $since = isset($notification->data['since'])
            ? CarbonImmutable::parse((string) $notification->data['since'])
            : CarbonImmutable::now()->subDays(2)->startOfDay();

        return $this->pull($integration, $since);
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return config("integrations.providers.{$this->key()}.{$key}", $default);
    }

    /** Constant-time comparison helper that throws on mismatch. */
    protected function assertSignature(?string $expected, ?string $given): void
    {
        if ($expected === null || $expected === '' || $given === null || $given === '' || ! hash_equals($expected, $given)) {
            throw new InvalidWebhookSignature("{$this->key()}: invalid webhook signature");
        }
    }

    /** Optional shared token in the webhook URL (?token=) for providers without payload signatures. */
    protected function assertUrlToken(Request $request): void
    {
        $token = $this->config('webhook_token');
        if (filled($token)) {
            $this->assertSignature((string) $token, (string) $request->query('token', ''));
        }
    }

    /**
     * @param  list<SampleData|null>  $samples
     * @return list<SampleData>
     */
    protected function compact(array $samples): array
    {
        return array_values(array_filter($samples));
    }
}
