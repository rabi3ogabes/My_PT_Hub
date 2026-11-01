<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/** Fitbit Web API + Subscriptions API (signed with X-Fitbit-Signature). */
class FitbitProvider extends AbstractHealthProvider
{
    public const API = 'https://api.fitbit.com';

    protected string $authorizeUrl = 'https://www.fitbit.com/oauth2/authorize';

    protected string $tokenUrl = 'https://api.fitbit.com/oauth2/token';

    protected string $servicesKey = 'fitbit';

    protected string $tokenAuth = 'basic';

    protected array $scopes = ['activity', 'heartrate', 'sleep', 'weight', 'profile'];

    public function key(): string
    {
        return 'fitbit';
    }

    public function afterConnect(Integration $integration): void
    {
        $subscriptionId = $this->subscriptionId($integration);
        $this->api($integration)->withHeaders(['X-Fitbit-Subscriber-Id' => (string) $this->config('subscriber_id', '1')])
            ->post(self::API."/1/user/-/apiSubscriptions/{$subscriptionId}.json");
        $integration->mergeSettings(['subscription_id' => $subscriptionId])->save();
    }

    public function revoke(Integration $integration): void
    {
        $subscriptionId = $integration->setting('subscription_id');
        if ($subscriptionId) {
            $this->api($integration)->delete(self::API."/1/user/-/apiSubscriptions/{$subscriptionId}.json");
        }
        Http::asForm()->withBasicAuth((string) $this->clientId(), (string) $this->clientSecret())
            ->post(self::API.'/oauth2/revoke', ['token' => $integration->refresh_token ?: $integration->access_token]);
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $samples = [];
        $day = CarbonImmutable::instance($since)->max(CarbonImmutable::now()->subDays(7))->startOfDay();
        $today = CarbonImmutable::now()->startOfDay();
        for (; $day->lte($today); $day = $day->addDay()) {
            array_push($samples, ...$this->pullDate($integration, $day->toDateString()));
        }

        return $samples;
    }

    /**
     * @return list<SampleData>
     */
    public function pullDate(Integration $integration, string $date, ?string $collection = null): array
    {
        $tz = (string) $integration->setting('timezone', $integration->user?->timezone ?: 'UTC');
        $dayStart = CarbonImmutable::parse($date, $tz)->startOfDay();
        $dayEnd = $dayStart->endOfDay();
        $samples = [];

        if ($collection === null || $collection === 'activities') {
            $summary = $this->getJson($integration, self::API."/1/user/-/activities/date/{$date}.json")['summary'] ?? [];
            if (isset($summary['steps'])) {
                $samples[] = SampleData::make('steps', $summary['steps'], 'count', $dayStart, $dayEnd, 'fitbit', "{$date}:steps");
            }
            if (isset($summary['activityCalories'])) {
                $samples[] = SampleData::make('active_kcal', $summary['activityCalories'], 'kcal', $dayStart, $dayEnd, 'fitbit', "{$date}:kcal");
            }
            if (isset($summary['restingHeartRate'])) {
                $samples[] = SampleData::make('resting_hr', $summary['restingHeartRate'], 'bpm', $dayStart, $dayEnd, 'fitbit', "{$date}:rhr");
            }
            $total = collect($summary['distances'] ?? [])->firstWhere('activity', 'total');
            if ($total) {
                $samples[] = SampleData::make('distance_m', (float) $total['distance'] * 1000, 'm', $dayStart, $dayEnd, 'fitbit', "{$date}:distance");
            }
        }

        if ($collection === null || $collection === 'sleep') {
            foreach ($this->getJson($integration, self::API."/1.2/user/-/sleep/date/{$date}.json")['sleep'] ?? [] as $log) {
                $samples[] = SampleData::make('sleep_minutes', $log['minutesAsleep'], 'min',
                    CarbonImmutable::parse($log['startTime'], $tz), CarbonImmutable::parse($log['endTime'], $tz), 'fitbit', $log['logId'],
                    ['efficiency' => $log['efficiency'] ?? null]);
            }
        }

        if ($collection === null || $collection === 'body') {
            foreach ($this->getJson($integration, self::API."/1/user/-/body/log/weight/date/{$date}.json")['weight'] ?? [] as $log) {
                $at = CarbonImmutable::parse($log['date'].' '.($log['time'] ?? '00:00:00'), $tz);
                $samples[] = SampleData::make('weight_kg', $log['weight'], 'kg', $at, $at, 'fitbit', $log['logId'].':weight');
                if (isset($log['fat'])) {
                    $samples[] = SampleData::make('body_fat_pct', $log['fat'], 'pct', $at, $at, 'fitbit', $log['logId'].':fat');
                }
            }
        }

        return $samples;
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        $expected = (string) config('services.fitbit.verify');

        return response()->noContent($expected !== '' && hash_equals($expected, (string) $request->query('verify')) ? 204 : 404);
    }

    public function handleWebhook(Request $request): array
    {
        $expected = base64_encode(hash_hmac('sha1', $request->getContent(), $this->clientSecret().'&', true));
        $this->assertSignature($expected, $request->header('X-Fitbit-Signature'));

        return array_values(array_map(fn (array $row) => new WebhookNotification((string) $row['ownerId'], data: [
            'date' => $row['date'] ?? null,
            'collection' => $row['collectionType'] ?? null,
        ]), array_filter((array) $request->json()->all(), 'is_array')));
    }

    public function fetchNotification(Integration $integration, WebhookNotification $notification): array
    {
        $date = $notification->data['date'] ?? CarbonImmutable::now()->toDateString();
        $collection = $notification->data['collection'] ?? null;

        return $this->pullDate($integration, (string) $date, in_array($collection, ['activities', 'sleep', 'body'], true) ? $collection : null);
    }

    private function subscriptionId(Integration $integration): string
    {
        return substr('aurum-'.$integration->id, 0, 50);
    }
}
