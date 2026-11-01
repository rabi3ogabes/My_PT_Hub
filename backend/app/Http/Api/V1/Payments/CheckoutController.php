<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Services\CheckoutService;
use App\Domain\Payments\Services\TaxCalculator;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Public, branded checkout for shareable payment links (no authentication). */
class CheckoutController extends ApiController
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly TaxCalculator $tax,
        private readonly TenantContext $context,
    ) {}

    /** @return array{0: Tenant, 1: Product} */
    private function resolve(string $tenantSlug, string $productSlug): array
    {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->where('type', 'business')->first();
        $product = $tenant ? Product::withoutGlobalScopes()->whereNull('deleted_at')->where('tenant_id', $tenant->id)->where('slug', $productSlug)->where('is_active', true)->first() : null;
        if (! $tenant || ! $product) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $product->setRelation('prices', Price::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('product_id', $product->id)->where('is_active', true)->orderBy('amount_minor')->get());

        return [$tenant, $product];
    }

    public function show(Request $request, string $tenantSlug, string $productSlug): JsonResponse
    {
        [$tenant, $product] = $this->resolve($tenantSlug, $productSlug);
        $coupon = $request->validate(['coupon' => ['nullable', 'string', 'max:32']])['coupon'] ?? null;

        return $this->context->run($tenant, fn () => $this->ok([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'logo_url' => $tenant->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null,
                'brand_color' => $tenant->brand_color,
                'country' => $tenant->country,
                'currency' => $tenant->currency,
                'locale' => $tenant->locale,
            ],
            'product' => (new ProductResource($product))->resolve($request),
            'tax_rate' => $this->tax->rateFor($tenant),
            'quotes' => $product->prices->map(fn (Price $price) => ['price_id' => $price->id] + $this->checkout->quote($tenant, $product, $price, $coupon))->values()->all(),
        ]));
    }

    public function store(Request $request, string $tenantSlug, string $productSlug): JsonResponse
    {
        [$tenant, $product] = $this->resolve($tenantSlug, $productSlug);
        $data = $request->validate([
            'price_id' => ['required', 'string', Rule::in($product->prices->pluck('id')->all())],
            'email' => ['required', 'email:rfc', 'max:190'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'coupon' => ['nullable', 'string', 'max:32'],
            'locale' => ['nullable', Rule::in(['en', 'ar', 'fr', 'es'])],
            'success_url' => ['nullable', 'url', 'max:1000'],
            'cancel_url' => ['nullable', 'url', 'max:1000'],
        ]);
        $price = $product->prices->firstWhere('id', $data['price_id']);

        $result = $this->checkout->checkout(
            $tenant, $product, $price,
            ['email' => $data['email'], 'name' => $data['name'], 'phone' => $data['phone'] ?? null, 'locale' => $data['locale'] ?? app()->getLocale()],
            $data['coupon'] ?? null,
            $request->header('Idempotency-Key'),
            $this->safeUrl($data['success_url'] ?? null),
            $this->safeUrl($data['cancel_url'] ?? null),
        );

        return $this->created(['order_id' => $result['order']->id, 'checkout_url' => $result['checkout_url'], 'status' => $result['order']->status]);
    }

    public function status(string $order): JsonResponse
    {
        $found = Order::withoutGlobalScopes()->whereKey($order)->first(['id', 'status', 'fulfilled_at']);
        if (! $found) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return $this->ok(['status' => $found->status, 'fulfilled' => $found->fulfilled_at !== null]);
    }

    /** Only our own host (or the app's deep-link scheme) may be a redirect target. */
    private function safeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $tenantDomain = config('app.tenant_domain');

        return $host && ($host === $appHost || ($tenantDomain && str_ends_with($host, '.'.$tenantDomain))) ? $url : null;
    }
}
