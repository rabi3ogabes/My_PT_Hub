<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\GatewayResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Connected payment gateways. Credentials are write-only (encrypted at rest, never returned). */
class GatewayController extends ApiController
{
    public function __construct(private readonly GatewayManager $gateways) {}

    public function index(): JsonResponse
    {
        Permission::assert($this->user(), 'payments.view', 'payments.manage');

        return $this->ok(GatewayResource::collection(PaymentGateway::query()->orderByDesc('is_default')->orderBy('created_at')->get())->resolve(request()), [
            'drivers' => $this->gateways->drivers(),
            'required_credentials' => GatewayManager::REQUIRED_CREDENTIALS,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'payments.manage');
        $data = $request->validate([
            'driver' => ['required', Rule::in($this->gateways->drivers())],
            ...$this->rules(),
        ]);
        $credentials = array_filter((array) ($data['credentials'] ?? []), fn ($v) => $v !== null && $v !== '');
        $this->assertCredentials($data['driver'], $credentials);

        $gateway = DB::transaction(function () use ($data, $credentials) {
            $gateway = PaymentGateway::create([
                'driver' => $data['driver'],
                'label' => $data['label'] ?? null,
                'credentials' => $credentials,
                'currencies' => isset($data['currencies']) ? array_values(array_unique(array_map('strtoupper', $data['currencies']))) : null,
                'is_default' => (bool) ($data['is_default'] ?? false),
                'is_live' => (bool) ($data['is_live'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);
            $this->normaliseDefaults($gateway);

            return $gateway;
        });
        ActivityLogger::log('payments.gateway_connected', $gateway, ['driver' => $gateway->driver]);

        return $this->created(new GatewayResource($gateway->refresh()));
    }

    public function update(Request $request, PaymentGateway $gateway): JsonResponse
    {
        Permission::assert($this->user(), 'payments.manage');
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($gateway, $data) {
            if (array_key_exists('credentials', $data)) {
                $merged = array_merge((array) ($gateway->credentials ?? []), (array) $data['credentials']);
                $merged = array_filter($merged, fn ($v) => $v !== null && $v !== '');
                $this->assertCredentials($gateway->driver, $merged);
                $gateway->credentials = $merged;
            }
            foreach (['label', 'is_default', 'is_live', 'is_active'] as $field) {
                if (array_key_exists($field, $data)) {
                    $gateway->{$field} = $data[$field];
                }
            }
            if (array_key_exists('currencies', $data)) {
                $gateway->currencies = $data['currencies'] ? array_values(array_unique(array_map('strtoupper', $data['currencies']))) : null;
            }
            $gateway->save();
            $this->normaliseDefaults($gateway);
        });
        ActivityLogger::log('payments.gateway_updated', $gateway, ['driver' => $gateway->driver, 'credentials_changed' => array_key_exists('credentials', $data)]);

        return $this->ok(new GatewayResource($gateway->refresh()));
    }

    public function destroy(PaymentGateway $gateway): JsonResponse
    {
        Permission::assert($this->user(), 'payments.manage');
        $inUse = ClientSubscription::query()->where('payment_gateway_id', $gateway->id)->whereIn('status', ['active', 'trialing', 'past_due', 'paused'])->exists();
        if ($inUse) {
            // Live subscriptions renew through this gateway: keep it, stop new checkouts.
            $gateway->update(['is_active' => false, 'is_default' => false]);
            ActivityLogger::log('payments.gateway_deactivated', $gateway, ['driver' => $gateway->driver]);

            return $this->ok(['deleted' => false, 'deactivated' => true]);
        }
        ActivityLogger::log('payments.gateway_removed', $gateway, ['driver' => $gateway->driver]);
        $gateway->delete();

        return $this->ok(['deleted' => true, 'deactivated' => false]);
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:80'],
            'credentials' => ['sometimes', 'array'],
            'credentials.*' => ['nullable'],
            'currencies' => ['nullable', 'array', 'max:30'],
            'currencies.*' => ['string', 'size:3', 'alpha'],
            'is_default' => ['sometimes', 'boolean'],
            'is_live' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $credentials */
    private function assertCredentials(string $driver, array $credentials): void
    {
        $missing = $this->gateways->missingCredentials($driver, $credentials);
        if ($missing !== []) {
            $errors = [];
            foreach ($missing as $key) {
                $errors["credentials.$key"] = [__('payments.errors.credential_required', ['key' => $key])];
            }
            throw ApiException::invalid('validation_failed', __('payments.errors.credentials_incomplete'), $errors);
        }
    }

    /** A currency has at most one default gateway. */
    private function normaliseDefaults(PaymentGateway $gateway): void
    {
        if (! $gateway->is_default) {
            return;
        }
        $mine = array_map('strtoupper', (array) ($gateway->currencies ?? []));
        PaymentGateway::query()->where('id', '!=', $gateway->id)->where('is_default', true)->get()
            ->filter(function (PaymentGateway $other) use ($mine) {
                $theirs = array_map('strtoupper', (array) ($other->currencies ?? []));

                return $mine === [] || $theirs === [] || array_intersect($mine, $theirs) !== [];
            })
            ->each(fn (PaymentGateway $other) => $other->update(['is_default' => false]));
    }
}
