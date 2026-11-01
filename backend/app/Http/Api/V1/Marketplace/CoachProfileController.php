<?php

namespace App\Http\Api\V1\Marketplace;

use App\Domain\Marketing\Models\Review;
use App\Domain\Marketing\Services\CoachDirectoryService;
use App\Domain\Marketing\Services\ReviewService;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Owner-side management of the public coach profile and review replies. */
class CoachProfileController extends ApiController
{
    public const LOCALES = ['en', 'ar', 'fr', 'es'];

    public function __construct(
        private readonly ReviewService $reviews,
        private readonly CoachDirectoryService $directory,
    ) {}

    public function show(): JsonResponse
    {
        return $this->ok($this->present($this->profile()));
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $this->profile();
        $data = $request->validate([
            'slug' => ['sometimes', 'string', 'min:3', 'max:80', 'alpha_dash:ascii', Rule::unique('coach_profiles', 'slug')->ignore($profile->id)],
            'headline' => ['sometimes', 'nullable', 'string', 'max:160'],
            'bio' => ['sometimes', 'nullable'],
            'bio.*' => ['nullable', 'string', 'max:5000'],
            'specialties' => ['sometimes', 'array', 'max:20'],
            'specialties.*' => ['string', 'max:40'],
            'languages' => ['sometimes', 'array', 'max:10'],
            'languages.*' => ['string', 'regex:/^[a-z]{2}$/'],
            'certifications' => ['sometimes', 'array', 'max:20'],
            'certifications.*.name' => ['required', 'string', 'max:120'],
            'certifications.*.issuer' => ['nullable', 'string', 'max:120'],
            'certifications.*.year' => ['nullable', 'integer', 'min:1950', 'max:'.now()->year],
            'city' => ['sometimes', 'nullable', 'string', 'max:80'],
            'country' => ['sometimes', 'nullable', 'string', 'size:2'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female'])],
            'offers_online' => ['sometimes', 'boolean'],
            'offers_in_person' => ['sometimes', 'boolean'],
            'price_from_minor' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:60', 'regex:/^@?[A-Za-z0-9._]+$/'],
            'intro_video_url' => ['sometimes', 'nullable', 'url:https', 'max:500'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('bio', $data)) {
            $bio = $data['bio'];
            if (is_string($bio)) {
                $profile->setTranslation('bio', app()->getLocale(), $bio);
            } elseif (is_array($bio)) {
                $profile->bio = array_filter(array_intersect_key($bio, array_flip(self::LOCALES)), fn ($v) => $v !== null && $v !== '') ?: null;
            } else {
                $profile->bio = null;
            }
            unset($data['bio']);
        }
        if (isset($data['specialties'])) {
            $data['specialties'] = array_values(array_unique(array_map(fn (string $s) => Str::snake(Str::ascii(trim($s))), $data['specialties'])));
        }
        if (isset($data['languages'])) {
            $data['languages'] = array_values(array_unique($data['languages']));
        }
        if (isset($data['certifications'])) {
            // Verified badges come from document checks only; keep them for unchanged certificates.
            $verified = collect($profile->certifications ?? [])->filter(fn ($c) => is_array($c) && ! empty($c['verified']))->pluck('name')->map(fn ($n) => mb_strtolower((string) $n))->all();
            $data['certifications'] = array_map(fn (array $c) => [
                'name' => $c['name'], 'issuer' => $c['issuer'] ?? null, 'year' => $c['year'] ?? null,
                'verified' => in_array(mb_strtolower($c['name']), $verified, true),
            ], $data['certifications']);
        }
        if (isset($data['country'])) {
            $data['country'] = strtoupper($data['country']);
        }
        if (isset($data['instagram'])) {
            $data['instagram'] = ltrim($data['instagram'], '@');
        }

        $profile->fill($data)->save();

        return $this->ok($this->present($profile->refresh()));
    }

    public function reply(Request $request, Review $review): JsonResponse
    {
        $data = $request->validate(['reply' => ['required', 'string', 'max:2000']]);
        $review = $this->reviews->reply($review, $data['reply']);

        return $this->ok($this->directory->reviewPayload($review->load('client:id,name')) + ['status' => $review->status]);
    }

    private function profile(): CoachProfile
    {
        $user = $this->user();
        $profile = CoachProfile::query()->where('user_id', $user->id)->first();
        if ($profile) {
            return $profile;
        }

        $base = Str::slug($user->name) ?: 'coach';
        $slug = $base;
        for ($i = 2; CoachProfile::withoutGlobalScopes()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return CoachProfile::create(['user_id' => $user->id, 'slug' => $slug, 'languages' => [$this->tenant()->locale ?: 'en']]);
    }

    /** @return array<string, mixed> */
    private function present(CoachProfile $profile): array
    {
        $profile->loadMissing(['user', 'tenant']);
        $reviews = Review::query()->with('client:id,name')->where('coach_profile_id', $profile->id)->latest()->limit(50)->get();

        return $this->directory->card($profile) + [
            'bio' => $profile->translate('bio'),
            'bio_translations' => (object) ($profile->bio ?? []),
            'certifications' => array_values($profile->certifications ?? []),
            'instagram' => $profile->instagram,
            'intro_video_url' => $profile->intro_video_url,
            'price_from_minor' => $profile->price_from_minor,
            'is_published' => $profile->is_published,
            'public_path' => '/coaches/'.$profile->slug,
            'reviews' => $reviews->map(fn (Review $r) => $this->directory->reviewPayload($r) + ['status' => $r->status])->all(),
            'review_stats' => [
                'pending' => $reviews->where('status', 'pending')->count(),
                'approved' => $reviews->where('status', 'approved')->count(),
                'rejected' => $reviews->where('status', 'rejected')->count(),
            ],
        ];
    }
}
