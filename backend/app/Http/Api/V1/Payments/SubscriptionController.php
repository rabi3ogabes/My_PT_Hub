<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Services\SubscriptionService;
use App\Domain\Payments\Support\Permission;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends ApiController
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'payments.view', 'payments.manage');
        $query = ClientSubscription::query()->with(['client', 'product', 'gateway'])
            ->when($request->input('filter.status'), fn ($q, $s) => $q->whereIn('status', explode(',', (string) $s)))
            ->when($request->input('filter.client_id'), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->boolean('filter.renewing_soon'), fn ($q) => $q->whereIn('status', ['active', 'trialing'])->where('cancel_at_period_end', false)->where('current_period_end', '<=', now()->addDays(7)))
            ->orderByDesc('created_at');

        return $this->paginated($query->paginate($this->perPage()), SubscriptionResource::class);
    }

    public function action(Request $request, ClientSubscription $subscription, string $action): JsonResponse
    {
        Permission::assert($this->user(), 'payments.manage', 'products.manage');
        $data = $request->validate(['immediately' => ['sometimes', 'boolean']]);

        $subscription = match ($action) {
            'cancel' => $this->subscriptions->cancel($subscription, ! ($data['immediately'] ?? false), $this->user()),
            'pause' => $this->subscriptions->pause($subscription, $this->user()),
            'resume' => $this->subscriptions->resume($subscription, $this->user()),
        };

        return $this->ok(new SubscriptionResource($subscription->load(['client', 'product', 'gateway'])));
    }
}
