<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Strava API v3: OAuth + push subscriptions (hub.verify_token handshake). */
class StravaProvider extends AbstractHealthProvider
{
    public const API = 'https://www.strava.com/api/v3';

    protected string $authorizeUrl = 'https://www.strava.com/oauth/authorize';

    protected string $tokenUrl = 'https://www.strava.com/oauth/token';

    protected string $servicesKey = 'strava';

    protected string $scopeSeparator = ',';

    protected array $scopes = ['read', 'activity:read_all'];

    protected array $authorizeParams = ['approval_prompt' => 'auto'];

    public function key(): string
    {
        return 'strava';
    }

    public function revoke(Integration $integration): void
    {
        $this->call($integration, 'post', 'https://www.strava.com/oauth/deauthorize');
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $samples = [];
        for ($page = 1; $page <= 5; $page++) {
            $activities = $this->getJson($integration, self::API.'/athlete/activities', [
                'after' => $since->getTimestamp(), 'per_page' => 100, 'page' => $page,
            ]);
            foreach ($activities as $activity) {
                array_push($samples, ...$this->mapActivity((array) $activity));
            }
            if (count($activities) < 100) {
                break;
            }
        }

        return $samples;
    }

    /**
     * @param  array<string, mixed>  $a
     * @return list<SampleData>
     */
    public function mapActivity(array $a): array
    {
        $start = CarbonImmutable::parse((string) $a['start_date']);
        $elapsed = (int) ($a['elapsed_time'] ?? $a['moving_time'] ?? 0);
        $moving = (int) ($a['moving_time'] ?? $elapsed);
        $type = strtolower((string) ($a['sport_type'] ?? $a['type'] ?? 'workout'));

        $samples = [SampleData::make('workout', round($moving / 60, 2), 'min', $start, $start->addSeconds($elapsed), 'strava', $a['id'], array_filter([
            'activity_type' => $type,
            'name' => $a['name'] ?? null,
            'distance_m' => $a['distance'] ?? null,
            'kcal' => $a['calories'] ?? null,
            'avg_hr' => $a['average_heartrate'] ?? null,
        ], fn ($v) => $v !== null))];

        return $samples;
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        $expected = (string) config('services.strava.verify_token');
        if ($request->query('hub_mode') !== 'subscribe' || $expected === '' || ! hash_equals($expected, (string) $request->query('hub_verify_token'))) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        return response()->json(['hub.challenge' => (string) $request->query('hub_challenge')]);
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        $expectedSubscription = $this->config('subscription_id');
        if (! isset($payload['owner_id'], $payload['object_type'])
            || (filled($expectedSubscription) && (string) ($payload['subscription_id'] ?? '') !== (string) $expectedSubscription)) {
            throw new InvalidWebhookSignature('strava: unknown subscription');
        }

        $owner = (string) $payload['owner_id'];
        if ($payload['object_type'] === 'athlete' && ($payload['updates']['authorized'] ?? null) === 'false') {
            return [new WebhookNotification($owner, deauthorized: true)];
        }
        if ($payload['object_type'] !== 'activity' || ($payload['aspect_type'] ?? '') === 'delete') {
            return [new WebhookNotification($owner, data: ['delete_external_id' => ($payload['aspect_type'] ?? '') === 'delete' ? (string) $payload['object_id'] : null])];
        }

        return [new WebhookNotification($owner, data: ['activity_id' => (string) $payload['object_id']])];
    }

    public function fetchNotification(Integration $integration, WebhookNotification $notification): array
    {
        $id = $notification->data['activity_id'] ?? null;
        if (! $id) {
            return [];
        }

        return $this->mapActivity($this->getJson($integration, self::API.'/activities/'.$id));
    }
}
