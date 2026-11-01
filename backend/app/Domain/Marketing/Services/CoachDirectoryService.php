<?php

namespace App\Domain\Marketing\Services;

use App\Domain\Marketing\Models\Review;
use App\Domain\Payments\Models\Product;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Support\Money;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Public coach directory: only published profiles of live tenants. Public
 * reads bypass the tenant scope explicitly and filter by tenant_id.
 */
final class CoachDirectoryService
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly PublicAvailabilityService $availability,
    ) {}

    /** @return Builder<CoachProfile> */
    public function published(): Builder
    {
        return CoachProfile::withoutGlobalScopes()
            ->with(['user', 'tenant'])
            ->where('coach_profiles.is_published', true)
            ->whereHas('tenant', fn (Builder $q) => $q->whereNull('deleted_at'))
            ->whereHas('user', fn (Builder $q) => $q->whereNull('deleted_at'));
    }

    /**
     * @param  array<string, mixed>  $filters  city, country, specialty, language, mode, price_min, price_max, gender, rating, q, verified
     * @return Builder<CoachProfile>
     */
    public function search(array $filters): Builder
    {
        $query = $this->published();

        if (filled($filters['city'] ?? null)) {
            $query->whereRaw('LOWER(coach_profiles.city) = ?', [mb_strtolower(trim((string) $filters['city']))]);
        }
        if (filled($filters['country'] ?? null)) {
            $query->where('coach_profiles.country', strtoupper((string) $filters['country']));
        }
        if (filled($filters['specialty'] ?? null)) {
            $query->whereJsonContains('coach_profiles.specialties', (string) $filters['specialty']);
        }
        if (filled($filters['language'] ?? null)) {
            $query->whereJsonContains('coach_profiles.languages', strtolower((string) $filters['language']));
        }
        match ($filters['mode'] ?? null) {
            'online' => $query->where('coach_profiles.offers_online', true),
            'in_person' => $query->where('coach_profiles.offers_in_person', true),
            default => null,
        };
        if (isset($filters['price_min']) && $filters['price_min'] !== null && $filters['price_min'] !== '') {
            $query->where('coach_profiles.price_from_minor', '>=', (int) $filters['price_min']);
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== null && $filters['price_max'] !== '') {
            $query->where('coach_profiles.price_from_minor', '<=', (int) $filters['price_max']);
        }
        if (filled($filters['gender'] ?? null)) {
            $query->where('coach_profiles.gender', (string) $filters['gender']);
        }
        if (filled($filters['rating'] ?? null)) {
            $query->where('coach_profiles.rating_avg', '>=', (float) $filters['rating']);
        }
        if (! empty($filters['verified'])) {
            $query->where('coach_profiles.is_verified', true);
        }
        if (filled($filters['q'] ?? null)) {
            $term = '%'.mb_strtolower(trim((string) $filters['q'])).'%';
            $query->where(fn (Builder $q) => $q->whereRaw('LOWER(coach_profiles.headline) LIKE ?', [$term])
                ->orWhereHas('user', fn (Builder $u) => $u->whereRaw('LOWER(name) LIKE ?', [$term])));
        }

        return $query->orderByDesc('coach_profiles.is_featured')
            ->orderByDesc('coach_profiles.rating_avg')
            ->orderByDesc('coach_profiles.rating_count')
            ->orderBy('coach_profiles.id');
    }

    public function findPublished(string $slug): CoachProfile
    {
        return $this->published()->where('coach_profiles.slug', $slug)->first()
            ?? throw new ApiException('not_found', __('marketplace.errors.coach_not_found'), 404);
    }

    /** @return array<string, mixed> */
    public function card(CoachProfile $profile): array
    {
        $tenant = $profile->tenant;
        $currency = $tenant?->currency ?? 'USD';

        return [
            'id' => $profile->id,
            'slug' => $profile->slug,
            'name' => $profile->user?->name,
            'avatar_url' => $profile->user?->avatar_path ? Storage::disk('public')->url($profile->user->avatar_path) : null,
            'headline' => $profile->headline,
            'business' => $tenant ? ['name' => $tenant->name, 'slug' => $tenant->slug, 'logo_url' => $tenant->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null] : null,
            'city' => $profile->city,
            'country' => $profile->country,
            'gender' => $profile->gender,
            'specialties' => array_values($profile->specialties ?? []),
            'languages' => array_values($profile->languages ?? []),
            'offers_online' => $profile->offers_online,
            'offers_in_person' => $profile->offers_in_person,
            'price_from' => $profile->price_from_minor !== null ? $this->money((int) $profile->price_from_minor, $currency) : null,
            'rating_avg' => round((float) $profile->rating_avg, 2),
            'rating_count' => (int) $profile->rating_count,
            'is_verified' => $profile->is_verified,
            'is_featured' => $profile->is_featured,
        ];
    }

    /** Full public profile: bio, certifications, packages, verified reviews and booking summary. */
    public function profile(CoachProfile $profile): array
    {
        $tenant = $profile->tenant ?? Tenant::query()->findOrFail($profile->tenant_id);

        return $this->context->run($tenant, function () use ($profile, $tenant) {
            $packages = Product::query()
                ->with(['prices' => fn ($q) => $q->where('is_active', true)->orderBy('amount_minor')])
                ->where('is_active', true)
                ->whereIn('type', (array) config('integrations.marketplace.package_types', []))
                ->orderBy('created_at')->limit(12)->get()
                ->filter(fn (Product $p) => $p->prices->isNotEmpty())
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'type' => $p->type,
                    'slug' => $p->slug,
                    'name' => $p->translate('name'),
                    'description' => $p->translate('description'),
                    'image_url' => $p->image_url,
                    'checkout_path' => '/public/checkout/'.$tenant->slug.'/'.$p->slug,
                    'prices' => $p->prices->map(fn ($price) => [
                        'id' => $price->id,
                        'type' => $price->type,
                        'amount' => $this->money((int) $price->amount_minor, $price->currency),
                        'interval' => $price->interval,
                        'interval_count' => (int) $price->interval_count,
                        'installments' => $price->installments,
                        'trial_days' => (int) $price->trial_days,
                    ])->values()->all(),
                ])->values()->all();

            $reviews = Review::query()->published()->with('client:id,name')
                ->where('coach_profile_id', $profile->id)->latest()->limit(20)->get()
                ->map(fn (Review $r) => $this->reviewPayload($r))->all();

            return $this->card($profile) + [
                'bio' => $profile->translate('bio'),
                'certifications' => array_values(array_map(fn ($c) => is_array($c) ? [
                    'name' => $c['name'] ?? null, 'issuer' => $c['issuer'] ?? null, 'year' => $c['year'] ?? null, 'verified' => (bool) ($c['verified'] ?? false),
                ] : ['name' => (string) $c, 'issuer' => null, 'year' => null, 'verified' => false], $profile->certifications ?? [])),
                'instagram' => $profile->instagram,
                'intro_video_url' => $profile->intro_video_url,
                'packages' => $packages,
                'reviews' => $reviews,
                'availability' => $this->availability->summary($profile),
            ];
        });
    }

    /** @return array<string, mixed> */
    public function reviewPayload(Review $review): array
    {
        $name = trim((string) $review->client?->name);
        $parts = preg_split('/\s+/', $name) ?: [];
        $display = $parts !== [] && $parts[0] !== '' ? $parts[0].(isset($parts[1]) ? ' '.mb_substr($parts[1], 0, 1).'.' : '') : null;

        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'body' => $review->body,
            'author' => $display,
            'is_verified' => $review->is_verified,
            'coach_reply' => $review->coach_reply,
            'replied_at' => $review->replied_at?->toIso8601String(),
            'created_at' => $review->created_at?->toIso8601String(),
        ];
    }

    /** @return array{amount_minor: int, currency: string, formatted: string} */
    private function money(int $minor, string $currency): array
    {
        return ['amount_minor' => $minor, 'currency' => $currency, 'formatted' => Money::format($minor, $currency)];
    }
}
