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

/** WHOOP Developer API: recovery, sleep and workouts; webhooks signed with X-WHOOP-Signature. */
class WhoopProvider extends AbstractHealthProvider
{
    public const API = 'https://api.prod.whoop.com/developer';

    protected string $authorizeUrl = 'https://api.prod.whoop.com/oauth/oauth2/auth';

    protected string $tokenUrl = 'https://api.prod.whoop.com/oauth/oauth2/token';

    protected string $servicesKey = 'whoop';

    protected array $scopes = ['offline', 'read:recovery', 'read:sleep', 'read:workout', 'read:profile'];

    public function key(): string
    {
        return 'whoop';
    }

    public function connect(string $code, array $context = []): TokenSet
    {
        $tokens = parent::connect($code, $context);
        $id = Http::withToken($tokens->accessToken)->acceptJson()->timeout(20)->get(self::API.'/v1/user/profile/basic')->json('user_id');

        return new TokenSet($tokens->accessToken, $tokens->refreshToken, $tokens->expiresAt, $id !== null ? (string) $id : null, $tokens->scopes);
    }

    public function revoke(Integration $integration): void
    {
        $this->call($integration, 'delete', self::API.'/v1/user/access');
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $query = ['start' => CarbonImmutable::instance($since)->utc()->toIso8601ZuluString(), 'limit' => 25];
        $samples = [];
        foreach (['recovery' => '/v1/recovery', 'sleep' => '/v1/activity/sleep', 'workout' => '/v1/activity/workout'] as $kind => $path) {
            $next = null;
            for ($page = 0; $page < 10; $page++) {
                $json = $this->getJson($integration, self::API.$path, array_filter($query + ['nextToken' => $next]));
                foreach ($json['records'] ?? [] as $record) {
                    array_push($samples, ...$this->mapRecord($kind, (array) $record));
                }
                $next = $json['next_token'] ?? null;
                if (! $next) {
                    break;
                }
            }
        }

        return $samples;
    }

    /**
     * @param  array<string, mixed>  $r
     * @return list<SampleData>
     */
    public function mapRecord(string $kind, array $r): array
    {
        $score = $r['score'] ?? [];

        return $this->compact(match ($kind) {
            'recovery' => [
                isset($score['recovery_score']) ? SampleData::make('readiness', $score['recovery_score'], 'score', (string) $r['created_at'], (string) $r['created_at'], 'whoop', $r['cycle_id'].':recovery') : null,
                isset($score['hrv_rmssd_milli']) ? SampleData::make('hrv', $score['hrv_rmssd_milli'], 'ms', (string) $r['created_at'], (string) $r['created_at'], 'whoop', $r['cycle_id'].':hrv') : null,
                isset($score['resting_heart_rate']) ? SampleData::make('resting_hr', $score['resting_heart_rate'], 'bpm', (string) $r['created_at'], (string) $r['created_at'], 'whoop', $r['cycle_id'].':rhr') : null,
            ],
            'sleep' => ($r['nap'] ?? false) ? [] : [
                SampleData::make('sleep_minutes', $this->sleepMinutes($score['stage_summary'] ?? [], (string) $r['start'], (string) $r['end']), 'min', (string) $r['start'], (string) $r['end'], 'whoop', $r['id']),
            ],
            'workout' => [
                SampleData::make('workout', CarbonImmutable::parse((string) $r['start'])->diffInSeconds(CarbonImmutable::parse((string) $r['end']), true) / 60, 'min', (string) $r['start'], (string) $r['end'], 'whoop', $r['id'], array_filter([
                    'activity_type' => isset($r['sport_id']) ? 'whoop_sport_'.$r['sport_id'] : null,
                    'strain' => $score['strain'] ?? null,
                    'kcal' => isset($score['kilojoule']) ? round($score['kilojoule'] / 4.184) : null,
                    'avg_hr' => $score['average_heart_rate'] ?? null,
                    'distance_m' => $score['distance_meter'] ?? null,
                ], fn ($v) => $v !== null)),
            ],
            default => [],
        });
    }

    /** @param array<string, mixed> $stages */
    private function sleepMinutes(array $stages, string $start, string $end): float
    {
        $asleep = ($stages['total_light_sleep_time_milli'] ?? 0) + ($stages['total_slow_wave_sleep_time_milli'] ?? 0) + ($stages['total_rem_sleep_time_milli'] ?? 0);

        return $asleep > 0 ? round($asleep / 60000, 2) : CarbonImmutable::parse($start)->diffInSeconds(CarbonImmutable::parse($end), true) / 60;
    }

    public function handleWebhook(Request $request): array
    {
        $timestamp = (string) $request->header('X-WHOOP-Signature-Timestamp');
        $expected = base64_encode(hash_hmac('sha256', $timestamp.$request->getContent(), (string) $this->clientSecret(), true));
        $this->assertSignature($expected, $request->header('X-WHOOP-Signature'));

        $payload = $request->json()->all();

        return [new WebhookNotification((string) $payload['user_id'], data: [
            'type' => (string) ($payload['type'] ?? ''),
            'id' => (string) ($payload['id'] ?? ''),
        ])];
    }

    public function fetchNotification(Integration $integration, WebhookNotification $notification): array
    {
        [$kind, $action] = array_pad(explode('.', (string) ($notification->data['type'] ?? '')), 2, '');
        $id = (string) ($notification->data['id'] ?? '');
        if ($action !== 'updated' || $id === '') {
            return [];
        }

        return match ($kind) {
            'workout' => $this->mapRecord('workout', $this->getJson($integration, self::API.'/v1/activity/workout/'.$id)),
            'sleep' => $this->mapRecord('sleep', $this->getJson($integration, self::API.'/v1/activity/sleep/'.$id)),
            'recovery' => $this->pull($integration, CarbonImmutable::now()->subDays(2)),
            default => [],
        };
    }
}
