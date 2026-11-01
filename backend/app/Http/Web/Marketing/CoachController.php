<?php

namespace App\Http\Web\Marketing;

use App\Domain\Marketing\Models\Review;
use App\Domain\Tenancy\Models\CoachProfile;
use App\View\Marketing\Catalog;
use App\View\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Public coach directory, city landing pages and profiles (the marketplace growth loop). */
class CoachController
{
    public static function query(): Builder
    {
        return CoachProfile::query()->withoutGlobalScopes()->where('is_published', true);
    }

    public function index(Request $request, string $locale): View
    {
        $filters = [
            'language' => in_array($request->query('language'), ['en', 'ar', 'fr', 'es'], true) ? $request->query('language') : null,
            'mode' => in_array($request->query('mode'), ['online', 'in_person'], true) ? $request->query('mode') : null,
        ];
        $coaches = $this->filtered(self::query(), $filters)->paginate(18)->withQueryString();
        $seo = Seo::make(__('marketing.coaches.meta_title'), __('marketing.coaches.meta_description'))
            ->breadcrumbs([[__('marketing.nav.res.coaches'), route('marketing.coaches.index')]]);
        if (array_filter($filters) !== []) {
            $seo->noindex();
        }

        return view('marketing.coaches.index', [
            'seo' => $seo,
            'coaches' => $coaches,
            'cities' => $this->cities(),
            'filters' => $filters,
            'city' => null,
        ]);
    }

    public function city(Request $request, string $locale, string $city): View
    {
        $cities = $this->cities();
        $cityName = $cities[$city] ?? (in_array($city, Catalog::CITIES, true) ? __('marketing.coaches.city_names.'.$city) : null);
        abort_if($cityName === null, 404);
        $names = array_keys(array_filter($this->rawCities(), fn ($slug) => $slug === $city));
        $coaches = self::query()->whereIn('city', $names ?: ['__none__'])->with('user:id,name,avatar_path')
            ->orderByDesc('is_featured')->orderByDesc('rating_avg')->paginate(18);

        $seo = Seo::make(__('marketing.coaches.city_title', ['city' => $cityName]), __('marketing.coaches.city_description', ['city' => $cityName]))
            ->breadcrumbs([[__('marketing.nav.res.coaches'), route('marketing.coaches.index')], [$cityName, url()->current()]])
            ->noindex($coaches->total() === 0)
            ->schema([
                '@type' => 'ItemList',
                'name' => __('marketing.coaches.city_title', ['city' => $cityName]),
                'itemListElement' => $coaches->getCollection()->values()->map(fn (CoachProfile $c, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => route('marketing.coaches.show', ['slug' => $c->slug]),
                    'name' => $c->user?->name,
                ])->all(),
            ]);

        return view('marketing.coaches.index', [
            'seo' => $seo,
            'coaches' => $coaches,
            'cities' => $cities,
            'filters' => ['language' => null, 'mode' => null],
            'city' => $cityName,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $coach = self::query()->where('slug', $slug)->with('user:id,name,avatar_path')->firstOrFail();
        $reviews = Review::query()->withoutGlobalScopes()
            ->where('coach_profile_id', $coach->id)->where('status', 'approved')
            ->with('client:id,name')->latest()->limit(20)->get();
        $count = Review::query()->withoutGlobalScopes()->where('coach_profile_id', $coach->id)->where('status', 'approved')->count();
        $avg = $count > 0 ? round((float) Review::query()->withoutGlobalScopes()->where('coach_profile_id', $coach->id)->where('status', 'approved')->avg('rating'), 1) : null;
        $name = (string) $coach->user?->name;
        $bio = (string) ($coach->translate('bio') ?? '');
        $avatar = $coach->user?->avatar_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($coach->user->avatar_path) : null;

        $person = array_filter([
            '@type' => 'Person',
            'name' => $name,
            'jobTitle' => $coach->headline ?: __('marketing.coaches.default_headline'),
            'description' => Str::limit(strip_tags($bio), 300),
            'image' => $avatar,
            'url' => url()->current(),
            'knowsLanguage' => $coach->languages ?: null,
            'sameAs' => $coach->instagram ? ['https://instagram.com/'.ltrim($coach->instagram, '@')] : null,
        ]);
        $seo = Seo::make(__('marketing.coaches.profile_title', ['name' => $name, 'headline' => $coach->headline ?: __('marketing.coaches.default_headline')]), Str::limit(strip_tags($bio) ?: __('marketing.coaches.profile_description', ['name' => $name]), 155))
            ->breadcrumbs([[__('marketing.nav.res.coaches'), route('marketing.coaches.index')], [$name, url()->current()]])
            ->image($avatar)
            ->type('profile')
            ->schema($person);

        if ($coach->offers_in_person && $coach->city) {
            $business = [
                '@type' => 'LocalBusiness',
                'name' => $name.' — '.($coach->headline ?: __('marketing.coaches.default_headline')),
                'url' => url()->current(),
                'image' => $avatar,
                'address' => array_filter(['@type' => 'PostalAddress', 'addressLocality' => $coach->city, 'addressCountry' => $coach->country]),
            ];
            if ($count > 0) {
                $business['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $avg, 'reviewCount' => $count, 'bestRating' => 5, 'worstRating' => 1];
            }
            $seo->schema(array_filter($business));
        }

        return view('marketing.coaches.show', compact('seo', 'coach', 'reviews', 'count', 'avg', 'name', 'bio', 'avatar'));
    }

    /** @param  array{language: ?string, mode: ?string}  $filters */
    private function filtered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['language'], fn ($q, $l) => $q->whereJsonContains('languages', $l))
            ->when($filters['mode'] === 'online', fn ($q) => $q->where('offers_online', true))
            ->when($filters['mode'] === 'in_person', fn ($q) => $q->where('offers_in_person', true))
            ->with('user:id,name,avatar_path')
            ->orderByDesc('is_featured')->orderByDesc('rating_avg')->orderBy('slug');
    }

    /** @return array<string, string> raw city name => slug */
    private function rawCities(): array
    {
        return Cache::remember('marketing:coach-cities', 600, fn () => self::query()->whereNotNull('city')->distinct()->pluck('city')
            ->mapWithKeys(fn ($c) => [(string) $c => Str::slug((string) $c)])->all());
    }

    /** @return array<string, string> slug => display name */
    public function cities(): array
    {
        $out = [];
        foreach ($this->rawCities() as $name => $slug) {
            $out[$slug] ??= $name;
        }
        ksort($out);

        return $out;
    }

    /** @return Collection<int, string> */
    public static function citySlugs(): Collection
    {
        return self::query()->whereNotNull('city')->distinct()->pluck('city')->map(fn ($c) => Str::slug((string) $c))->filter()->unique()->values();
    }
}
