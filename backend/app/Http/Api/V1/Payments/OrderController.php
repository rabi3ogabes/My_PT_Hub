<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Identity\Models\User;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Services\CheckoutService;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends ApiController
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly CheckoutService $checkout,
        private readonly GatewayManager $gateways,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'payments.view', 'payments.manage');
        $query = Order::query()->with(['client', 'items', 'gateway'])
            ->when($request->input('filter.status'), fn ($q, $s) => $q->whereIn('status', explode(',', (string) $s)))
            ->when($request->input('filter.client_id'), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->input('filter.gateway'), fn ($q, $d) => $q->whereHas('gateway', fn ($g) => $g->where('driver', $d)))
            ->when($request->input('filter.from'), fn ($q, $d) => $q->where('created_at', '>=', $d))
            ->when($request->input('filter.to'), fn ($q, $d) => $q->where('created_at', '<=', $d.' 23:59:59'))
            ->when($request->input('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('customer_email', 'like', "%{$term}%")->orWhere('customer_name', 'like', "%{$term}%")->orWhere('id', $term)))
            ->orderByDesc('created_at');

        return $this->paginated($query->paginate($this->perPage()), OrderResource::class);
    }

    public function show(Order $order): JsonResponse
    {
        Permission::assert($this->user(), 'payments.view', 'payments.manage');

        return $this->ok(new OrderResource($order->load(['client', 'items', 'gateway', 'payments.refunds', 'invoices'])));
    }

    public function refund(Request $request, Order $order): JsonResponse
    {
        Permission::assert($this->user(), 'payments.manage');
        $data = $request->validate([
            'amount_minor' => ['nullable', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:250'],
            'revoke_access' => ['sometimes', 'boolean'],
        ]);
        $this->payments->refund($order, $data['amount_minor'] ?? null, $data['reason'] ?? null, $this->user(), (bool) ($data['revoke_access'] ?? false));

        return $this->ok(new OrderResource($order->refresh()->load(['client', 'items', 'gateway', 'payments.refunds', 'invoices'])));
    }

    /**
     * Records a cash / bank-transfer payment (with optional receipt photo) — either
     * settling an existing pending order or selling a product on the spot. Runs the
     * same fulfilment pipeline as online payments.
     */
    public function manual(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'payments.manage', 'products.manage');
        $data = $request->validate([
            'order_id' => ['nullable', 'string', 'size:26'],
            'product_id' => ['required_without:order_id', 'nullable', 'string', 'size:26'],
            'price_id' => ['required_without:order_id', 'nullable', 'string', 'size:26'],
            'client_id' => ['nullable', 'string', 'size:26'],
            'email' => ['required_without_all:order_id,client_id', 'nullable', 'email', 'max:190'],
            'name' => ['nullable', 'string', 'max:120'],
            'coupon' => ['nullable', 'string', 'max:32'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer', 'card_terminal', 'other'])],
            'amount_minor' => ['nullable', 'integer', 'min:0'],
            'paid_at' => ['nullable', 'date', 'before_or_equal:now'],
            'reference' => ['nullable', 'string', 'max:120'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,pdf', 'max:10240'],
        ]);
        $tenant = $this->tenant();

        if (! empty($data['order_id'])) {
            $order = Order::query()->findOrFail($data['order_id']);
            if ($order->status !== 'pending') {
                throw ApiException::conflict('order_not_pending', __('payments.errors.order_not_pending'));
            }
        } else {
            $product = Product::query()->findOrFail($data['product_id']);
            $price = Price::query()->where('product_id', $product->id)->findOrFail($data['price_id']);
            $client = null;
            if (! empty($data['client_id'])) {
                $client = User::find($data['client_id']);
                if (! $client || ! $client->isClientIn($tenant->id)) {
                    throw ApiException::invalid('validation_failed', __('payments.errors.not_a_client'), ['client_id' => [__('payments.errors.not_a_client')]]);
                }
            }
            $order = $this->checkout->createOrder(
                $tenant, $product, $price,
                ['client' => $client, 'email' => $data['email'] ?? null, 'name' => $data['name'] ?? null],
                $data['coupon'] ?? null,
                $request->header('Idempotency-Key'),
                $this->gateways->manual($tenant),
                ['channel' => 'manual'],
            );
        }

        $receipt = $request->file('receipt')?->store('payments/receipts/'.$tenant->id, 'local');
        $this->payments->markPaid($order, [
            'driver' => 'manual',
            'method' => $data['method'],
            'amount_minor' => $data['amount_minor'] ?? $order->total_minor,
            'gateway_payment_id' => $data['reference'] ?? null,
            'receipt_path' => $receipt ?: null,
            'recorded_by' => $this->user()->id,
            'paid_at' => $data['paid_at'] ?? null,
        ]);

        return $this->created(new OrderResource($order->refresh()->load(['client', 'items', 'gateway', 'payments.refunds', 'invoices'])));
    }
}
