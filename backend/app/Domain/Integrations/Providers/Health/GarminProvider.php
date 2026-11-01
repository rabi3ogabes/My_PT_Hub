<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Garmin Health API (OAuth 2.0 PKCE). Data is pushed to our webhook as
 * summary arrays (dailies, activities, sleeps, bodyComps, hrv); backfill is
 * requested asynchronously and also arrives by push.
 */
class GarminProvider extends AbstractHealthProvider
{
    public const API = 'https://apis.garmin.com/wellness-api/rest';

    protected string $authorizeUrl = 'https://connect.garmin.com/oauth2Confirm';

    protected string $tokenUrl = 'https://diauth.garmin.com/di-oauth2-service/oauth/token';

    protected string $servicesKey = 'garmin';

    public function key(): string
    {
        return 'garmin';
    }

    public function usesPkce(): bool
    {
        return true;
    }

    public function connect(string $code, array $context = []): TokenSet
    {
        $tokens = parent::connect($code, $context);
        $userId = Http::withToken($tokens->accessToken)->acceptJson()->timeout(20)
            ->get(self::API.'/user/id')->json('userId');

        return new TokenSet($tokens->accessToken, $tokens->refreshToken, $tokens->expiresAt, $userId ? (string) $userId : null, $tokens->scopes);
    }

    public function revoke(Integration $integration): void
    {
        $this->call($integration, 'delete', self::API.'/user/registration');
    }

    public function backfill(Integration $integration, int $days): array
    {
        $end = CarbonImmutable::now();
        $start = $end->subDays(min($days, 90));
        foreach (['dailies', 'activities', 'sleeps', 'bodyComps', 'hrv'] as $summary) {
            $this->call($integration, 'get', self::API.'/backfill/'.$summary, [
                'summaryStartTimeInSeconds' => $start->getTimestamp(),
                'summaryEndTimeInSeconds' => $end->getTimestamp(),
            ]);
        }

        return [];
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $samples = [];
        $cursor = CarbonImmutable::instance($since)->max(CarbonImmutable::now()->subDays(7));
        $now = CarbonImmutable::now();
        while ($cursor->lt($now)) {
            $windowEnd = $cursor->addDay()->min($now);
            foreach (['dailies', 'activities', 'sleeps', 'bodyComps', 'hrv'] as $summary) {
                $rows = $this->getJson($integration, self::API.'/'.$summary, [
                    'uploadStartTimeInSeconds' => $cursor->getTimestamp(),
                    'uploadEndTimeInSeconds' => $windowEnd->getTimestamp(),
                ]);
                foreach ($this->mapSummaries([$summary => $rows]) as $userSamples) {
                    array_push($samples, ...$userSamples);
                }
            }
            $cursor = $windowEnd;
        }

        return $samples;
    }

    public function handleWebhook(Request $request): array
    {
        $this->assertUrlToken($request);
        $payload = $request->json()->all();
        if (! is_array($payload) || $payload === []) {
            throw new InvalidWebhookSignature('garmin: empty payload');
        }

        $notifications = [];
        foreach ($payload['deregistrations'] ?? [] as $row) {
            $notifications[] = new WebhookNotification((string) $row['userId'], deauthorized: true);
        }
        foreach ($this->mapSummaries($payload) as $userId => $samples) {
            $notifications[] = new WebhookNotification((string) $userId, $samples);
        }

        return $notifications;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, list<SampleData>> samples grouped by Garmin userId
     */
    public function mapSummaries(array $payload): array
    {
        $out = [];
        $add = function (array $row, ?SampleData $sample) use (&$out): void {
            if ($sample) {
                $out[(string) ($row['userId'] ?? '')][] = $sample;
            }
        };

        foreach ($payload['dailies'] ?? [] as $row) {
            $start = (int) $row['startTimeInSeconds'];
            $end = $start + (int) ($row['durationInSeconds'] ?? 86400);
            $id = (string) $row['summaryId'];
            $add($row, isset($row['steps']) ? SampleData::make('steps', $row['steps'], 'count', $start, $end, 'garmin', $id.':steps') : null);
            $add($row, isset($row['activeKilocalories']) ? SampleData::make('active_kcal', $row['activeKilocalories'], 'kcal', $start, $end, 'garmin', $id.':kcal') : null);
            $add($row, isset($row['distanceInMeters']) ? SampleData::make('distance_m', $row['distanceInMeters'], 'm', $start, $end, 'garmin', $id.':distance') : null);
            $add($row, isset($row['restingHeartRateInBeatsPerMinute']) ? SampleData::make('resting_hr', $row['restingHeartRateInBeatsPerMinute'], 'bpm', $start, $end, 'garmin', $id.':rhr') : null);
        }
        foreach ($payload['activities'] ?? [] as $row) {
            $start = (int) $row['startTimeInSeconds'];
            $duration = (int) ($row['durationInSeconds'] ?? 0);
            $add($row, SampleData::make('workout', round($duration / 60, 2), 'min', $start, $start + $duration, 'garmin', $row['summaryId'] ?? $row['activityId'], array_filter([
                'activity_type' => strtolower((string) ($row['activityType'] ?? 'other')),
                'name' => $row['activityName'] ?? null,
                'distance_m' => $row['distanceInMeters'] ?? null,
                'kcal' => $row['activeKilocalories'] ?? null,
                'avg_hr' => $row['averageHeartRateInBeatsPerMinute'] ?? null,
            ], fn ($v) => $v !== null)));
        }
        foreach ($payload['sleeps'] ?? [] as $row) {
            $start = (int) $row['startTimeInSeconds'];
            $duration = (int) ($row['durationInSeconds'] ?? 0);
            $add($row, SampleData::make('sleep_minutes', round($duration / 60, 2), 'min', $start, $start + $duration, 'garmin', $row['summaryId']));
        }
        foreach ($payload['bodyComps'] ?? [] as $row) {
            $at = (int) $row['measurementTimeInSeconds'];
            $add($row, isset($row['weightInGrams']) ? SampleData::make('weight_kg', $row['weightInGrams'] / 1000, 'kg', $at, $at, 'garmin', $row['summaryId'].':weight') : null);
            $add($row, isset($row['bodyFatInPercent']) ? SampleData::make('body_fat_pct', $row['bodyFatInPercent'], 'pct', $at, $at, 'garmin', $row['summaryId'].':fat') : null);
        }
        foreach ($payload['hrv'] ?? [] as $row) {
            $start = (int) $row['startTimeInSeconds'];
            $add($row, isset($row['lastNightAvg']) ? SampleData::make('hrv', $row['lastNightAvg'], 'ms', $start, $start + (int) ($row['durationInSeconds'] ?? 0), 'garmin', $row['summaryId']) : null);
        }

        unset($out['']);

        return $out;
    }
}
