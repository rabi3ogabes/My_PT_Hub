<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Payments\Models\Coupon;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\CouponResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage', 'payments.view');
        $query = Coupon::query()
            ->when($request->has('filter.active'), fn ($q) => $q->where('is_active', $request->boolean('filter.active')))
            ->when($request->input('q'), fn ($q, $term) => $q->where('code', 'like', '%'.strtoupper($term).'%'))
            ->orderByDesc('created_at');

        return $this->paginated($query->paginate($this->perPage()), CouponResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage');
        $data = $this->validated($request, null);

        return $this->created(new CouponResource(Coupon::create($data)));
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage');
        $coupon->update($this->validated($request, $coupon));

        return $this->ok(new CouponResource($coupon->refresh()));
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage');
        $coupon->redemptions > 0 ? $coupon->update(['is_active' => false]) : $coupon->delete();

        return $this->noContent();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Coupon $coupon): array
    {
        $req = $coupon ? 'sometimes' : 'required';
        $data = $request->validate([
            'code' => [$req, 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9_-]+$/'],
            'type' => [$req, Rule::in(['percent', 'fixed'])],
            'value' => [$req, 'integer', 'min:1', 'max:100000000'],
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'duration' => ['sometimes', Rule::in(['once', 'repeating', 'forever'])],
            'duration_months' => ['nullable', 'required_if:duration,repeating', 'integer', 'min:1', 'max:36'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string', 'size:26'],
            'expires_at' => ['nullable', 'date'],
            'referrer_id' => ['nullable', 'string', 'size:26'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
            $taken = Coupon::query()->where('code', $data['code'])->when($coupon, fn ($q) => $q->where('id', '!=', $coupon->id))->exists();
            if ($taken) {
                throw ApiException::invalid('validation_failed', __('payments.coupon.code_taken'), ['code' => [__('payments.coupon.code_taken')]]);
            }
        }
        $type = $data['type'] ?? $coupon?->type;
        if ($type === 'percent' && ($data['value'] ?? $coupon?->value) > 100) {
            throw ApiException::invalid('validation_failed', __('payments.coupon.percent_max'), ['value' => [__('payments.coupon.percent_max')]]);
        }
        if ($type === 'fixed' && empty($data['currency'] ?? $coupon?->currency)) {
            $data['currency'] = $this->tenant()->currency;
        }
        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }
        if (! empty($data['product_ids']) && Product::query()->whereIn('id', $data['product_ids'])->count() !== count(array_unique($data['product_ids']))) {
            throw ApiException::invalid('validation_failed', __('validation.exists', ['attribute' => 'product_ids']), ['product_ids' => [__('validation.exists', ['attribute' => 'product_ids'])]]);
        }
        if (! empty($data['referrer_id']) && \App\Domain\Identity\Models\User::find($data['referrer_id'])?->roleIn($this->tenant()->id) === null) {
            throw ApiException::invalid('validation_failed', __('validation.exists', ['attribute' => 'referrer_id']), ['referrer_id' => [__('validation.exists', ['attribute' => 'referrer_id'])]]);
        }

        return $data;
    }
}
