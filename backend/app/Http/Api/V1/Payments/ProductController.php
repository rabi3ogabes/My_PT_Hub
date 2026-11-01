<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Habits\Models\Habit;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientGroup;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Payments\StaffProductResource;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Catalogue: products with nested prices, fulfilment config and shareable payment links. */
class ProductController extends ApiController
{
    public const TYPES = ['coaching_package', 'membership', 'credit_pack', 'single_session', 'digital_program', 'physical', 'event_ticket'];

    public function index(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage', 'payments.view');
        $query = Product::query()->with(['prices', 'tenant'])
            ->when($request->input('filter.type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->has('filter.active'), fn ($q) => $q->where('is_active', $request->boolean('filter.active')))
            ->when($request->input('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('slug', 'like', '%'.Str::slug($term).'%')->orWhere('name', 'like', '%'.$term.'%')))
            ->orderByDesc('created_at');

        return $this->paginated($query->paginate($this->perPage()), StaffProductResource::class);
    }

    public function show(Product $product): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage', 'payments.view');

        return $this->ok(new StaffProductResource($product->load(['prices', 'tenant'])));
    }

    public function store(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage');
        $data = $request->validate($this->rules(creating: true));
        $this->assertFulfilment($data['fulfilment'] ?? []);

        $product = DB::transaction(function () use ($data) {
            $name = $this->translatable($data['name']);
            $product = Product::create([
                'type' => $data['type'],
                'slug' => $this->uniqueSlug($data['slug'] ?? ($name['en'] ?? (array_values($name)[0] ?? 'product'))),
                'name' => $name,
                'description' => isset($data['description']) ? $this->translatable($data['description']) : null,
                'image_url' => $data['image_url'] ?? null,
                'fulfilment' => $this->fulfilment($data['fulfilment'] ?? []),
                'grace_days' => $data['grace_days'] ?? 3,
                'sell_in_app_ios' => $data['sell_in_app_ios'] ?? 'hidden',
                'is_active' => $data['is_active'] ?? true,
            ]);
            $this->syncPrices($product, $data['prices']);

            return $product;
        });

        return $this->created(new StaffProductResource($product->load(['prices', 'tenant'])));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage');
        $data = $request->validate($this->rules(creating: false));
        if (array_key_exists('fulfilment', $data)) {
            $this->assertFulfilment($data['fulfilment'] ?? []);
        }

        DB::transaction(function () use ($product, $data) {
            $attributes = [];
            foreach (['type', 'image_url', 'grace_days', 'sell_in_app_ios', 'is_active'] as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $data[$field];
                }
            }
            if (array_key_exists('name', $data)) {
                $attributes['name'] = $this->translatable($data['name'], $product->name);
            }
            if (array_key_exists('description', $data)) {
                $attributes['description'] = $data['description'] === null ? null : $this->translatable($data['description'], $product->description);
            }
            if (array_key_exists('fulfilment', $data)) {
                $attributes['fulfilment'] = $this->fulfilment($data['fulfilment'] ?? []);
            }
            if (! empty($data['slug']) && $data['slug'] !== $product->slug) {
                $attributes['slug'] = $this->uniqueSlug($data['slug'], $product->id);
            }
            $product->update($attributes);
            if (array_key_exists('prices', $data)) {
                $this->syncPrices($product, $data['prices']);
            }
        });

        return $this->ok(new StaffProductResource($product->refresh()->load(['prices', 'tenant'])));
    }

    public function destroy(Product $product): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage');
        $product->update(['is_active' => false]);
        $product->delete();

        return $this->noContent();
    }

    /** Shareable payment link (WhatsApp / Instagram), QR code and embeddable button. */
    public function link(Product $product): JsonResponse
    {
        Permission::assert($this->user(), 'products.manage', 'payments.view');
        $tenant = $this->tenant();
        $url = url('checkout/'.$tenant->slug.'/'.$product->slug);
        $qr = (new QRCode(new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'outputBase64' => true,
            'svgAddXmlHeader' => true,
        ])))->render($url);
        $name = (string) $product->translate('name');

        return $this->ok([
            'url' => $url,
            'api_url' => url('api/v1/public/checkout/'.$tenant->slug.'/'.$product->slug),
            'qr_svg' => $qr,
            'whatsapp_url' => 'https://wa.me/?text='.rawurlencode($name.' — '.$url),
            'embed_html' => '<a href="'.e($url).'" target="_blank" rel="noopener" style="display:inline-block;padding:12px 20px;border-radius:12px;background:'.e($tenant->brand_color).';color:#0B0B0F;font-weight:600;text-decoration:none">'.e(__('payments.checkout.buy_now')).'</a>',
        ]);
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return [
            'type' => [$req, Rule::in(self::TYPES)],
            'name' => [$req, function ($attr, $value, $fail) {
                if (! (is_string($value) && trim($value) !== '' && mb_strlen($value) <= 160) && ! (is_array($value) && array_filter($value, 'is_string') !== [])) {
                    $fail(__('validation.required', ['attribute' => 'name']));
                }
            }],
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash'],
            'description' => ['nullable'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'grace_days' => ['sometimes', 'integer', 'min:0', 'max:90'],
            'sell_in_app_ios' => ['sometimes', Rule::in(['hidden', 'external', 'iap'])],
            'is_active' => ['sometimes', 'boolean'],
            'fulfilment' => ['nullable', 'array'],
            'fulfilment.program_id' => ['nullable', 'string', 'size:26'],
            'fulfilment.meal_plan_id' => ['nullable', 'string', 'size:26'],
            'fulfilment.habit_ids' => ['nullable', 'array', 'max:20'],
            'fulfilment.habit_ids.*' => ['string', 'size:26'],
            'fulfilment.checkin_form_id' => ['nullable', 'string', 'size:26'],
            'fulfilment.checkin_weekday' => ['nullable', 'integer', 'between:1,7'],
            'fulfilment.group_id' => ['nullable', 'string', 'size:26'],
            'fulfilment.coach_id' => ['nullable', 'string', 'size:26'],
            'fulfilment.credits' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'fulfilment.credits_expire_days' => ['nullable', 'integer', 'min:1', 'max:1095'],
            'fulfilment.access_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'fulfilment.welcome_message' => ['nullable'],
            'prices' => [$creating ? 'required' : 'sometimes', 'array', 'min:1', 'max:10'],
            'prices.*.id' => ['nullable', 'string', 'size:26'],
            'prices.*.type' => ['required', Rule::in(['one_time', 'recurring', 'installments'])],
            'prices.*.amount_minor' => ['required', 'integer', 'min:0', 'max:100000000'],
            'prices.*.currency' => ['required', 'string', 'size:3', 'alpha'],
            'prices.*.interval' => ['nullable', 'required_unless:prices.*.type,one_time', Rule::in(['week', 'month', 'year'])],
            'prices.*.interval_count' => ['nullable', 'integer', 'min:1', 'max:12'],
            'prices.*.installments' => ['nullable', 'required_if:prices.*.type,installments', 'integer', 'min:2', 'max:24'],
            'prices.*.trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'prices.*.setup_fee_minor' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'prices.*.tax_inclusive' => ['nullable', 'boolean'],
            'prices.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $f */
    private function assertFulfilment(array $f): void
    {
        $errors = [];
        $check = function (string $key, bool $exists) use (&$errors) {
            if (! $exists) {
                $errors["fulfilment.$key"] = [__('validation.exists', ['attribute' => $key])];
            }
        };
        if (! empty($f['program_id']) && class_exists(\App\Domain\Workouts\Models\Program::class)) {
            $check('program_id', \App\Domain\Workouts\Models\Program::query()->whereKey($f['program_id'])->exists());
        }
        if (! empty($f['meal_plan_id'])) {
            $check('meal_plan_id', MealPlan::query()->whereKey($f['meal_plan_id'])->exists());
        }
        if (! empty($f['habit_ids'])) {
            $check('habit_ids', Habit::query()->whereIn('id', $f['habit_ids'])->count() === count(array_unique($f['habit_ids'])));
        }
        if (! empty($f['checkin_form_id'])) {
            $check('checkin_form_id', CheckinForm::query()->whereKey($f['checkin_form_id'])->exists());
        }
        if (! empty($f['group_id'])) {
            $check('group_id', ClientGroup::query()->whereKey($f['group_id'])->exists());
        }
        if (! empty($f['coach_id'])) {
            $check('coach_id', \App\Domain\Identity\Models\User::query()->whereKey($f['coach_id'])->first()?->isStaffIn($this->tenant()->id) === true);
        }
        if ($errors !== []) {
            throw ApiException::invalid('validation_failed', __('payments.errors.fulfilment_invalid'), $errors);
        }
    }

    /**
     * @param  array<string, mixed>  $f
     * @return array<string, mixed>|null
     */
    private function fulfilment(array $f): ?array
    {
        $allowed = ['program_id', 'meal_plan_id', 'habit_ids', 'checkin_form_id', 'checkin_weekday', 'group_id', 'coach_id', 'credits', 'credits_expire_days', 'access_days', 'welcome_message'];
        $clean = array_filter(array_intersect_key($f, array_flip($allowed)), fn ($v) => $v !== null && $v !== '' && $v !== []);
        if (isset($clean['welcome_message'])) {
            $clean['welcome_message'] = is_array($clean['welcome_message'])
                ? array_map(fn ($v) => mb_substr((string) $v, 0, 2000), array_intersect_key($clean['welcome_message'], array_flip(['en', 'ar', 'fr', 'es'])))
                : mb_substr((string) $clean['welcome_message'], 0, 2000);
        }

        return $clean ?: null;
    }

    /** @param list<array<string, mixed>> $prices */
    private function syncPrices(Product $product, array $prices): void
    {
        $keep = [];
        foreach ($prices as $data) {
            $price = ! empty($data['id']) ? $product->prices()->whereKey($data['id'])->first() : null;
            if (! empty($data['id']) && ! $price) {
                throw ApiException::invalid('validation_failed', __('validation.exists', ['attribute' => 'price']), ['prices' => [__('validation.exists', ['attribute' => 'price'])]]);
            }
            $attributes = [
                'type' => $data['type'],
                'amount_minor' => (int) $data['amount_minor'],
                'currency' => strtoupper($data['currency']),
                'interval' => $data['type'] === 'one_time' ? null : ($data['interval'] ?? 'month'),
                'interval_count' => (int) ($data['interval_count'] ?? 1),
                'installments' => $data['type'] === 'installments' ? (int) $data['installments'] : null,
                'trial_days' => $data['type'] === 'recurring' ? (int) ($data['trial_days'] ?? 0) : 0,
                'setup_fee_minor' => (int) ($data['setup_fee_minor'] ?? 0),
                'tax_inclusive' => (bool) ($data['tax_inclusive'] ?? true),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ];
            if ($price) {
                $price->update($attributes);
            } else {
                $price = Price::create($attributes + ['product_id' => $product->id]);
            }
            $keep[] = $price->id;
        }
        // Prices referenced by orders cannot be deleted: retire the ones left out.
        $product->prices()->whereNotIn('id', $keep)->update(['is_active' => false]);
    }

    /**
     * @param  array<string, mixed>|null  $existing
     * @return array<string, mixed>
     */
    private function translatable(mixed $value, ?array $existing = null): array
    {
        if (is_array($value)) {
            return array_map(fn ($v) => is_string($v) ? mb_substr($v, 0, 5000) : $v, array_intersect_key($value, array_flip(['en', 'ar', 'fr', 'es'])));
        }
        $current = $existing ?? [];
        $current[app()->getLocale()] = mb_substr((string) $value, 0, 5000);

        return $current;
    }

    private function uniqueSlug(string $source, ?string $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'product';
        $slug = $base;
        $i = 1;
        while (Product::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
