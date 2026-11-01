<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Gateways\Drivers\FlutterwaveGateway;
use App\Domain\Payments\Gateways\Drivers\HyperPayGateway;
use App\Domain\Payments\Gateways\Drivers\ManualGateway;
use App\Domain\Payments\Gateways\Drivers\MyFatoorahGateway;
use App\Domain\Payments\Gateways\Drivers\PaymobGateway;
use App\Domain\Payments\Gateways\Drivers\PayPalGateway;
use App\Domain\Payments\Gateways\Drivers\PayTabsGateway;
use App\Domain\Payments\Gateways\Drivers\StripeGateway;
use App\Domain\Payments\Gateways\Drivers\TapGateway;
use App\Domain\Payments\Models\PaymentGateway as GatewayModel;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use Closure;

/** Resolves a tenant's connected gateways to driver instances (default per currency). */
final class GatewayManager
{
    /** @var array<string, class-string<PaymentGateway>> */
    public const DRIVERS = [
        'stripe' => StripeGateway::class,
        'paypal' => PayPalGateway::class,
        'tap' => TapGateway::class,
        'paytabs' => PayTabsGateway::class,
        'hyperpay' => HyperPayGateway::class,
        'myfatoorah' => MyFatoorahGateway::class,
        'paymob' => PaymobGateway::class,
        'flutterwave' => FlutterwaveGateway::class,
        'manual' => ManualGateway::class,
    ];

    /** Credentials each driver needs before it can take payments. */
    public const REQUIRED_CREDENTIALS = [
        'stripe' => ['webhook_secret'],
        'paypal' => ['client_id', 'client_secret', 'webhook_id'],
        'tap' => ['secret_key'],
        'paytabs' => ['profile_id', 'server_key'],
        'hyperpay' => ['access_token', 'entity_id', 'webhook_key'],
        'myfatoorah' => ['api_key', 'webhook_secret'],
        'paymob' => ['secret_key', 'public_key', 'hmac_secret', 'integration_ids'],
        'flutterwave' => ['secret_key', 'secret_hash'],
        'manual' => [],
    ];

    /** @var array<string, Closure(GatewayModel): PaymentGateway> */
    private array $extensions = [];

    /** @param Closure(GatewayModel): PaymentGateway $factory */
    public function extend(string $driver, Closure $factory): void
    {
        $this->extensions[$driver] = $factory;
    }

    /** @return list<string> */
    public function drivers(): array
    {
        return array_keys(self::DRIVERS);
    }

    public function for(GatewayModel $model): PaymentGateway
    {
        if (isset($this->extensions[$model->driver])) {
            return ($this->extensions[$model->driver])($model);
        }
        $class = self::DRIVERS[$model->driver] ?? throw new ApiException('unknown_gateway', __('payments.errors.unknown_gateway'), 422);

        return new $class($model);
    }

    /** The tenant's gateway for a currency: the default for that currency first, then any active gateway accepting it. */
    public function resolve(Tenant $tenant, string $currency, ?string $preferredId = null): GatewayModel
    {
        $currency = strtoupper($currency);
        $gateways = GatewayModel::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (GatewayModel $g) => $this->accepts($g, $currency));

        if ($preferredId && ($preferred = $gateways->firstWhere('id', $preferredId))) {
            return $preferred;
        }
        $online = $gateways->where('driver', '!=', 'manual');

        return $online->first() ?? $gateways->first()
            ?? throw new ApiException('no_gateway', __('payments.errors.no_gateway', ['currency' => $currency]), 422);
    }

    public function driverFor(Tenant $tenant, string $currency): PaymentGateway
    {
        return $this->for($this->resolve($tenant, $currency));
    }

    public function accepts(GatewayModel $gateway, string $currency): bool
    {
        $configured = array_map('strtoupper', (array) ($gateway->currencies ?? []));
        if ($configured !== []) {
            return in_array($currency, $configured, true);
        }
        $supported = $this->for($gateway)->supportedCurrencies();

        return $supported === [] || in_array($currency, $supported, true);
    }

    /** The tenant's manual gateway (created on first use). */
    public function manual(Tenant $tenant): GatewayModel
    {
        return GatewayModel::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('driver', 'manual')->orderByDesc('is_active')->first()
            ?? GatewayModel::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'driver' => 'manual',
                'label' => __('payments.gateways.manual'),
                'credentials' => [],
                'is_default' => false,
                'is_live' => true,
                'is_active' => true,
            ]);
    }

    /**
     * @param  array<string, mixed>  $credentials
     * @return list<string> missing keys
     */
    public function missingCredentials(string $driver, array $credentials): array
    {
        $missing = array_values(array_filter(self::REQUIRED_CREDENTIALS[$driver] ?? [], fn ($k) => empty($credentials[$k])));
        if ($driver === 'stripe' && empty($credentials['account_id']) && empty($credentials['secret_key'])) {
            $missing[] = 'account_id';
        }

        return $missing;
    }
}
