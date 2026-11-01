<?php

namespace App\Http\Api\V1\Integrations;

use App\Domain\Billing\Services\PlanGate;
use App\Domain\Integrations\Models\WebhookEndpoint;
use App\Domain\Integrations\Services\OutgoingWebhooks;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\WebhookDeliveryResource;
use App\Http\Api\V1\Resources\WebhookEndpointResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Outgoing webhook endpoints (also used by the Zapier/Make REST-hook subscriptions). */
class WebhookEndpointController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorizeManage();

        return $this->ok(WebhookEndpointResource::collection(WebhookEndpoint::query()->latest()->get())->resolve(request()), [
            'events' => config('integrations.webhooks.events'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate($this->rules(true));
        app(PlanGate::class)->assertFeature($this->tenant(), 'api');
        if (WebhookEndpoint::query()->count() >= 25) {
            throw ApiException::limit(__('integrations.errors.too_many_endpoints'));
        }

        $secret = OutgoingWebhooks::generateSecret();
        $endpoint = WebhookEndpoint::create([
            'url' => $data['url'],
            'description' => $data['description'] ?? null,
            'events' => array_values(array_unique($data['events'])),
            'is_active' => $data['is_active'] ?? true,
            'secret' => $secret,
        ]);
        ActivityLogger::log('webhook_endpoint.created', $endpoint, ['url' => $endpoint->url]);

        // The signing secret is returned exactly once.
        return $this->created(WebhookEndpointResource::make($endpoint)->resolve($request) + ['secret' => $secret]);
    }

    public function update(Request $request, WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorizeManage();
        $data = $request->validate($this->rules(false) + ['rotate_secret' => ['sometimes', 'boolean']]);

        $payload = collect($data)->only(['url', 'description', 'is_active'])->all();
        if (isset($data['events'])) {
            $payload['events'] = array_values(array_unique($data['events']));
        }
        if (array_key_exists('is_active', $payload) && $payload['is_active']) {
            $payload['disabled_at'] = null;
        }
        $secret = null;
        if (! empty($data['rotate_secret'])) {
            $secret = OutgoingWebhooks::generateSecret();
            $payload['secret'] = $secret;
        }
        $webhookEndpoint->update($payload);
        ActivityLogger::log('webhook_endpoint.updated', $webhookEndpoint, ['rotated' => $secret !== null]);

        $out = WebhookEndpointResource::make($webhookEndpoint->refresh())->resolve($request);

        return $this->ok($secret ? $out + ['secret' => $secret] : $out);
    }

    public function destroy(WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorizeManage();
        ActivityLogger::log('webhook_endpoint.deleted', $webhookEndpoint, ['url' => $webhookEndpoint->url]);
        $webhookEndpoint->delete();

        return $this->noContent();
    }

    public function deliveries(Request $request, WebhookEndpoint $endpoint): JsonResponse
    {
        $this->authorizeManage();
        $status = $request->input('filter.status');

        $page = $endpoint->deliveries()
            ->when(in_array($status, ['pending', 'delivered', 'failed'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage());

        return $this->paginated($page, WebhookDeliveryResource::class);
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return [
            'url' => [$req, 'string', 'max:1024', 'url:https', function (string $attribute, mixed $value, \Closure $fail) {
                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));
                if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')
                    || (filter_var($host, FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
                    $fail(__('integrations.errors.private_url'));
                }
            }],
            'description' => ['sometimes', 'nullable', 'string', 'max:190'],
            'events' => [$req, 'array', 'min:1'],
            'events.*' => ['string', Rule::in([...config('integrations.webhooks.events'), '*'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function authorizeManage(): void
    {
        $user = $this->user();
        if (! $user->isOwnerIn($this->tenant()->id) && ! $user->can('integrations.manage')) {
            throw ApiException::forbidden();
        }
    }
}
