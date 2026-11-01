<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/** Oura API v2 with webhook subscriptions (x-oura-signature HMAC-SHA256). */
class OuraProvider extends AbstractHealthProvider
{
    public const API = 'https://api.ouraring.com/v2';

    /** Collections we read and the sample mapper for each. */
    private const COLLECTIONS = ['daily_activity', 'sleep', 'daily_readiness', 'workout'];

    protected string $authorizeUrl = 'https://cloud.ouraring.com/oauth/authorize';

    protected string $tokenUrl = 'https://api.ouraring.com/oauth/token';

    protected string $servicesKey = 'oura';

    protected array $scopes = ['personal', 'daily', 'heartrate', 'workout', 'session'];

    public function key(): string
    {
        return 'oura';
    }

    public function connect(string $code, array $context = []): TokenSet
    {
        $tokens = parent::connect($code, $context);
        $id = Http::withToken($tokens->accessToken)->acceptJson()->timeout(20)->get(self::API.'/usercollection/personal_info')->json('id');

        return new TokenSet($tokens->accessToken, $tokens->refreshToken, $tokens->expiresAt, $id ? (string) $id : null, $tokens->scopes);
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $query = ['start_date' => $since->toDateString(), 'end_date' => CarbonImmutable::now()->addDay()->toDateString()];
        $samples = [];
        foreach (self::COLLECTIONS as $collection) {
            foreach ($this->getJson($integration, self::API.'/usercollection/'.$collection, $query)['data'] ?? [] as $doc) {
                array_push($samples, ...$this->mapDocument($collection, (array) $doc));
            }
        }

        return $samples;
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return list<SampleData>
     */
    public function mapDocument(string $collection, array $doc): array
    {
        $id = (string) ($doc['id'] ?? '');
        $dayStart = isset($doc['day']) ? CarbonImmutable::parse((string) $doc['day'])->startOfDay() : null;

        return $this->compact(match ($collection) {
            'daily_activity' => [
                isset($doc['steps']) ? SampleData::make('steps', $doc['steps'], 'count', $dayStart, $dayStart->endOfDay(), 'oura', $id.':steps') : null,
                isset($doc['active_calories']) ? SampleData::make('active_kcal', $doc['active_calories'], 'kcal', $dayStart, $dayStart->endOfDay(), 'oura', $id.':kcal') : null,
            ],
            'sleep' => ($doc['type'] ?? 'long_sleep') === 'deleted' ? [] : [
                SampleData::make('sleep_minutes', round(((int) ($doc['total_sleep_duration'] ?? 0)) / 60, 2), 'min', (string) $doc['bedtime_start'], (string) $doc['bedtime_end'], 'oura', $id),
                isset($doc['average_hrv']) ? SampleData::make('hrv', $doc['average_hrv'], 'ms', (string) $doc['bedtime_start'], (string) $doc['bedtime_end'], 'oura', $id.':hrv') : null,
                isset($doc['lowest_heart_rate']) ? SampleData::make('resting_hr', $doc['lowest_heart_rate'], 'bpm', (string) $doc['bedtime_start'], (string) $doc['bedtime_end'], 'oura', $id.':rhr') : null,
            ],
            'daily_readiness' => [
                isset($doc['score']) ? SampleData::make('readiness', $doc['score'], 'score', $dayStart, $dayStart, 'oura', $id) : null,
            ],
            'workout' => [
                SampleData::make('workout', CarbonImmutable::parse((string) $doc['start_datetime'])->diffInSeconds(CarbonImmutable::parse((string) $doc['end_datetime']), true) / 60, 'min',
                    (string) $doc['start_datetime'], (string) $doc['end_datetime'], 'oura', $id, array_filter([
                        'activity_type' => $doc['activity'] ?? null, 'kcal' => $doc['calories'] ?? null, 'distance_m' => $doc['distance'] ?? null,
                    ], fn ($v) => $v !== null)),
            ],
            default => [],
        });
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        $expected = (string) $this->config('verification_token');
        if ($expected === '' || ! hash_equals($expected, (string) $request->query('verification_token'))) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        return response()->json(['challenge' => (string) $request->query('challenge')]);
    }

    public function handleWebhook(Request $request): array
    {
        $timestamp = (string) $request->header('x-oura-timestamp');
        $expected = strtoupper(hash_hmac('sha256', $timestamp.$request->getContent(), (string) $this->clientSecret()));
        $this->assertSignature($expected, strtoupper((string) $request->header('x-oura-signature')));

        $payload = $request->json()->all();

        return [new WebhookNotification((string) $payload['user_id'], data: [
            'data_type' => (string) ($payload['data_type'] ?? ''),
            'object_id' => (string) ($payload['object_id'] ?? ''),
            'event_type' => (string) ($payload['event_type'] ?? ''),
        ])];
    }

    public function fetchNotification(Integration $integration, WebhookNotification $notification): array
    {
        $type = $notification->data['data_type'] ?? '';
        $objectId = $notification->data['object_id'] ?? '';
        if (! in_array($type, self::COLLECTIONS, true) || $objectId === '' || ($notification->data['event_type'] ?? '') === 'delete') {
            return [];
        }

        return $this->mapDocument($type, $this->getJson($integration, self::API.'/usercollection/'.$type.'/'.$objectId));
    }
}
