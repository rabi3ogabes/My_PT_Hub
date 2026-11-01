<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

/**
 * Polar AccessLink v3. Tokens are long-lived (no refresh); users must be
 * registered once; webhooks are signed with Polar-Webhook-Signature.
 */
class PolarProvider extends AbstractHealthProvider
{
    public const API = 'https://www.polaraccesslink.com/v3';

    protected string $authorizeUrl = 'https://flow.polar.com/oauth2/authorization';

    protected string $tokenUrl = 'https://polarremote.com/v2/oauth2/token';

    protected string $servicesKey = 'polar';

    protected string $tokenAuth = 'basic';

    protected array $scopes = ['accesslink.read_all'];

    public function key(): string
    {
        return 'polar';
    }

    public function refresh(Integration $integration): TokenSet
    {
        throw new ProviderAuthException('polar: tokens cannot be refreshed; the user must reconnect');
    }

    public function afterConnect(Integration $integration): void
    {
        $response = $this->call($integration, 'post', self::API.'/users', ['member-id' => $integration->user_id]);
        if ($response->failed() && $response->status() !== 409) {
            throw new ProviderException("polar: user registration failed ({$response->status()})");
        }
    }

    public function revoke(Integration $integration): void
    {
        if ($integration->external_user_id) {
            $this->call($integration, 'delete', self::API.'/users/'.$integration->external_user_id);
        }
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $samples = [];
        foreach ($this->getJson($integration, self::API.'/exercises') as $exercise) {
            $mapped = $this->mapExercise((array) $exercise);
            if ($mapped && $mapped->startAt->gte($since)) {
                $samples[] = $mapped;
            }
        }

        return $samples;
    }

    /** @param array<string, mixed> $e */
    public function mapExercise(array $e): ?SampleData
    {
        if (! isset($e['start_time'], $e['duration'])) {
            return null;
        }
        $offset = (int) ($e['start_time_utc_offset'] ?? 0);
        $start = CarbonImmutable::parse((string) $e['start_time'], 'UTC')->subMinutes($offset);
        $seconds = $this->isoDurationSeconds((string) $e['duration']);

        return SampleData::make('workout', round($seconds / 60, 2), 'min', $start, $start->addSeconds($seconds), 'polar', $e['id'], array_filter([
            'activity_type' => isset($e['sport']) ? strtolower((string) $e['sport']) : null,
            'kcal' => $e['calories'] ?? null,
            'distance_m' => $e['distance'] ?? null,
            'avg_hr' => $e['heart_rate']['average'] ?? null,
        ], fn ($v) => $v !== null));
    }

    private function isoDurationSeconds(string $iso): int
    {
        if (! preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:([\d.]+)S)?$/', $iso, $m)) {
            return 0;
        }

        return (int) (((int) ($m[1] ?? 0)) * 3600 + ((int) ($m[2] ?? 0)) * 60 + (float) ($m[3] ?? 0));
    }

    public function handleWebhook(Request $request): array
    {
        $secret = (string) $this->config('webhook_secret');
        $this->assertSignature($secret === '' ? null : hash_hmac('sha256', $request->getContent(), $secret), $request->header('Polar-Webhook-Signature'));

        $payload = $request->json()->all();
        if (($payload['event'] ?? '') === 'PING') {
            return [];
        }

        return [new WebhookNotification((string) $payload['user_id'], data: [
            'event' => (string) ($payload['event'] ?? ''),
            'entity_id' => (string) ($payload['entity_id'] ?? ''),
        ])];
    }

    public function fetchNotification(Integration $integration, WebhookNotification $notification): array
    {
        if (($notification->data['event'] ?? '') !== 'EXERCISE' || empty($notification->data['entity_id'])) {
            return $this->pull($integration, CarbonImmutable::now()->subDays(2));
        }
        $sample = $this->mapExercise($this->getJson($integration, self::API.'/exercises/'.$notification->data['entity_id']));

        return $sample ? [$sample] : [];
    }
}
