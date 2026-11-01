<?php

namespace App\Domain\Integrations\Providers\Health;

use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Withings Public API: body composition + activity, Notify subscriptions. */
class WithingsProvider extends AbstractHealthProvider
{
    public const API = 'https://wbsapi.withings.net';

    /** appli codes: 1 weight/body, 16 activity, 44 sleep. */
    private const APPLIS = [1, 16, 44];

    protected string $authorizeUrl = 'https://account.withings.com/oauth2_user/authorize2';

    protected string $tokenUrl = 'https://wbsapi.withings.net/v2/oauth2';

    protected string $servicesKey = 'withings';

    protected string $scopeSeparator = ',';

    protected array $scopes = ['user.metrics', 'user.activity'];

    public function key(): string
    {
        return 'withings';
    }

    protected function tokenRequest(array $params): array
    {
        return parent::tokenRequest(['action' => 'requesttoken'] + $params);
    }

    protected function unwrapTokenResponse(array $json): array
    {
        $status = (int) ($json['status'] ?? -1);
        if ($status !== 0) {
            throw in_array($status, [100, 101, 102, 200, 401, 503], true)
                ? new ProviderAuthException("withings: token request rejected (status {$status})")
                : new ProviderException("withings: token request failed (status {$status})");
        }

        return parent::unwrapTokenResponse((array) $json['body']);
    }

    public function afterConnect(Integration $integration): void
    {
        foreach (self::APPLIS as $appli) {
            $this->call($integration, 'post', self::API.'/notify', ['action' => 'subscribe', 'callbackurl' => $this->callbackUrl(), 'appli' => $appli]);
        }
    }

    public function revoke(Integration $integration): void
    {
        foreach (self::APPLIS as $appli) {
            $this->call($integration, 'post', self::API.'/notify', ['action' => 'revoke', 'callbackurl' => $this->callbackUrl(), 'appli' => $appli]);
        }
    }

    private function callbackUrl(): string
    {
        $token = $this->config('webhook_token');

        return route('integrations.webhook', array_filter(['provider' => 'withings', 'token' => $token]));
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function body(Integration $integration, string $path, array $params): array
    {
        $json = (array) $this->api($integration)->asForm()->post(self::API.$path, $params)->json();
        $status = (int) ($json['status'] ?? -1);
        if ($status === 401) {
            throw new ProviderAuthException('withings: access token rejected');
        }
        if ($status !== 0) {
            throw new ProviderException("withings: {$path} failed (status {$status})");
        }

        return (array) ($json['body'] ?? []);
    }

    public function pull(Integration $integration, CarbonInterface $since): array
    {
        $samples = [];
        $measures = $this->body($integration, '/measure', ['action' => 'getmeas', 'meastypes' => '1,6', 'category' => 1, 'lastupdate' => $since->getTimestamp()]);
        foreach ($measures['measuregrps'] ?? [] as $group) {
            $at = (int) $group['date'];
            foreach ($group['measures'] ?? [] as $m) {
                $value = $m['value'] * (10 ** $m['unit']);
                $samples[] = match ((int) $m['type']) {
                    1 => SampleData::make('weight_kg', $value, 'kg', $at, $at, 'withings', $group['grpid'].':1'),
                    6 => SampleData::make('body_fat_pct', $value, 'pct', $at, $at, 'withings', $group['grpid'].':6'),
                    default => null,
                };
            }
        }

        $activity = $this->body($integration, '/v2/measure', [
            'action' => 'getactivity', 'startdateymd' => $since->toDateString(), 'enddateymd' => CarbonImmutable::now()->toDateString(),
            'data_fields' => 'steps,calories,distance',
        ]);
        foreach ($activity['activities'] ?? [] as $day) {
            $start = CarbonImmutable::parse((string) $day['date'], (string) ($day['timezone'] ?? 'UTC'))->startOfDay();
            $samples[] = isset($day['steps']) ? SampleData::make('steps', $day['steps'], 'count', $start, $start->endOfDay(), 'withings', $day['date'].':steps') : null;
            $samples[] = isset($day['calories']) ? SampleData::make('active_kcal', $day['calories'], 'kcal', $start, $start->endOfDay(), 'withings', $day['date'].':kcal') : null;
            $samples[] = isset($day['distance']) ? SampleData::make('distance_m', $day['distance'], 'm', $start, $start->endOfDay(), 'withings', $day['date'].':distance') : null;
        }

        return $this->compact($samples);
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        return response()->noContent(200);
    }

    public function handleWebhook(Request $request): array
    {
        $this->assertUrlToken($request);
        $userId = $request->input('userid');
        if (! $userId) {
            throw new InvalidWebhookSignature('withings: missing userid');
        }
        $start = $request->input('startdate');

        return [new WebhookNotification((string) $userId, data: [
            'appli' => (int) $request->input('appli'),
            'since' => $start ? CarbonImmutable::createFromTimestampUTC((int) $start)->subMinute()->toIso8601String() : null,
        ])];
    }
}
