<?php

namespace App\Http\Web\Marketing;

use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Public, branded checkout page for coach payment links. Quotes, order
 * creation and status polling go through the public JSON API
 * (/api/v1/public/checkout/... and /api/v1/public/orders/{id}/status).
 */
class CheckoutPageController
{
    public function __invoke(Request $request, string $tenantSlug, string $productSlug): Response
    {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->where('type', 'business')->first();
        $product = $tenant ? Product::withoutGlobalScopes()->whereNull('deleted_at')
            ->where('tenant_id', $tenant->id)->where('slug', $productSlug)->where('is_active', true)->first() : null;
        abort_if(! $tenant || ! $product, 404);

        $labels = Price::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('product_id', $product->id)->where('is_active', true)->get()
            ->mapWithKeys(fn (Price $p) => [$p->id => $this->label($p)])->all();

        $color = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $tenant->brand_color) ? $tenant->brand_color : '#D4AF6A';
        $self = route('checkout.show', ['tenantSlug' => $tenantSlug, 'productSlug' => $productSlug]);

        return response()->view('checkout.show', [
            'tenant' => $tenant,
            'product' => $product,
            'name' => (string) $product->translate('name'),
            'description' => (string) ($product->translate('description') ?? ''),
            'color' => $color,
            'logoUrl' => $tenant->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null,
            'config' => [
                'quoteUrl' => url("/api/v1/public/checkout/{$tenantSlug}/{$productSlug}"),
                'statusUrl' => url('/api/v1/public/orders/__ID__/status'),
                'successUrl' => $self.'?return=success',
                'cancelUrl' => $self.'?return=cancelled',
                'locale' => app()->getLocale(),
                'order' => is_string($request->query('order')) ? $request->query('order') : null,
                'returned' => $request->query('return'),
                'labels' => $labels,
            ],
        ])->header('Cache-Control', 'no-store')->header('X-Robots-Tag', 'noindex');
    }

    private function label(Price $price): string
    {
        if ($price->interval && in_array($price->interval, ['day', 'week', 'month', 'year'], true)) {
            $count = max(1, (int) $price->interval_count);
            $label = trans_choice('web.checkout.every.'.$price->interval, $count, ['n' => $count]);
            if ((int) $price->trial_days > 0) {
                $label .= ' · '.trans_choice('web.checkout.trial', (int) $price->trial_days, ['n' => (int) $price->trial_days]);
            }

            return $label;
        }
        if ((int) $price->installments > 1) {
            return trans_choice('web.checkout.installments', (int) $price->installments, ['n' => (int) $price->installments]);
        }

        return __('web.checkout.once');
    }
}
