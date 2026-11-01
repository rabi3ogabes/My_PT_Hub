<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Services\CheckoutService;
use App\Domain\Payments\Services\InvoiceService;
use App\Domain\Payments\Services\SubscriptionService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Payments\InvoiceController;
use App\Http\Api\V1\Resources\Payments\ClientInvoiceResource;
use App\Http\Api\V1\Resources\Payments\OrderResource;
use App\Http\Api\V1\Resources\Payments\ProductResource;
use App\Http\Api\V1\Resources\Payments\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/** Client shop: the coach's products, checkout, purchases, invoices and self-service subscription management. */
class ShopController extends ApiController
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly SubscriptionService $subscriptions,
        private readonly InvoiceService $invoices,
    ) {}

    /** Apple guidelines: digital-only products are hidden (or IAP) inside the iOS app per product setting. */
    private function isIos(Request $request): bool
    {
        $platform = strtolower((string) $request->input('platform', $request->header('X-Platform', '')));
        if ($platform !== '') {
            return $platform === 'ios';
        }

        return (bool) preg_match('/\b(iPhone|iPad|iPod|iOS)\b|CFNetwork|Darwin/i', (string) $request->userAgent());
    }

    public function index(Request $request): JsonResponse
    {
        $ios = $this->isIos($request);
        $products = Product::query()->where('is_active', true)
            ->when($ios, fn ($q) => $q->where('sell_in_app_ios', '!=', 'hidden'))
            ->with(['prices' => fn ($q) => $q->where('is_active', true)->orderBy('amount_minor')])
            ->whereHas('prices', fn ($q) => $q->where('is_active', true))
            ->orderBy('created_at')
            ->get();

        return $this->ok(ProductResource::collection($products)->resolve($request), ['platform' => $ios ? 'ios' : 'other']);
    }

    public function checkout(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'price_id' => ['required', 'string', 'size:26'],
            'coupon' => ['nullable', 'string', 'max:32'],
            'platform' => ['nullable', Rule::in(['ios', 'android', 'web'])],
        ]);
        if ($this->isIos($request)) {
            if ($product->sell_in_app_ios === 'hidden') {
                throw ApiException::forbidden(__('payments.errors.not_available_on_ios'));
            }
            if ($product->sell_in_app_ios === 'iap') {
                throw ApiException::invalid('use_in_app_purchase', __('payments.errors.use_in_app_purchase'));
            }
        }
        $price = $product->prices()->whereKey($data['price_id'])->first()
            ?? throw ApiException::invalid('validation_failed', __('validation.exists', ['attribute' => 'price_id']), ['price_id' => [__('validation.exists', ['attribute' => 'price_id'])]]);

        $result = $this->checkout->checkout(
            $this->tenant(), $product, $price,
            ['client' => $this->user(), 'locale' => $this->user()->preferredLocale()],
            $data['coupon'] ?? null,
            $request->header('Idempotency-Key'),
        );

        return $this->created(['order_id' => $result['order']->id, 'checkout_url' => $result['checkout_url'], 'status' => $result['order']->status]);
    }

    public function purchases(): JsonResponse
    {
        $orders = Order::query()->where('client_id', $this->user()->id)->whereIn('status', ['paid', 'partially_refunded', 'refunded', 'pending'])
            ->with(['items', 'invoices', 'payments.refunds'])->orderByDesc('created_at')->limit(100)->get();
        $subs = ClientSubscription::query()->where('client_id', $this->user()->id)->with('product')->orderByDesc('created_at')->get();

        return $this->ok([
            'orders' => OrderResource::collection($orders)->resolve(request()),
            'subscriptions' => SubscriptionResource::collection($subs)->resolve(request()),
            'credits' => CreditLedgerEntry::balanceFor($this->user()->id),
        ]);
    }

    public function invoices(): JsonResponse
    {
        $query = Invoice::query()->where('client_id', $this->user()->id)->orderByDesc('issued_at')->orderByDesc('id');

        return $this->paginated($query->cursorPaginate($this->perPage()), ClientInvoiceResource::class);
    }

    public function invoicePdf(Invoice $invoice): Response
    {
        if ($invoice->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return InvoiceController::pdfResponse($this->invoices->pdf($invoice, $this->user()->preferredLocale()), $invoice->number);
    }

    /** Two taps in the app (confirm sheet + this call): ends at period end, no retention maze. */
    public function cancelSubscription(ClientSubscription $subscription): JsonResponse
    {
        $this->own($subscription);

        return $this->ok(new SubscriptionResource($this->subscriptions->cancel($subscription, true, $this->user())->load('product')));
    }

    public function pauseSubscription(ClientSubscription $subscription): JsonResponse
    {
        $this->own($subscription);

        return $this->ok(new SubscriptionResource($this->subscriptions->requestPause($subscription, $this->user())->load('product')));
    }

    private function own(ClientSubscription $subscription): void
    {
        if ($subscription->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
    }
}
