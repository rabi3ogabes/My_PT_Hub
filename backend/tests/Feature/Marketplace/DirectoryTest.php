<?php

use Illuminate\Support\Facades\Http;
use App\Domain\Calendar\Models\AvailabilityRule;
use App\Domain\Marketing\Models\Review;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Tenant;

beforeEach(function () {
    Http::preventStrayRequests();
});

/** Creates a coaching business whose owner profile has the given attributes. */
function coachWithProfile($test, array $profile = [], array $tenant = []): array
{
    [$t, $owner] = (fn () => $this->createCoachTenant([], $tenant + ['currency' => 'QAR']))->call($test);
    $model = CoachProfile::withoutGlobalScopes()->where('tenant_id', $t->id)->firstOrFail();
    $model->forceFill(array_merge(['is_published' => true, 'city' => 'Doha', 'country' => 'QA', 'languages' => ['ar', 'en'], 'specialties' => ['strength']], $profile))->save();

    return [$t, $owner, $model->refresh()];
}

it('lists only published coaches, featured first, filtered by city and language', function () {
    [, , $doha] = coachWithProfile($this, ['headline' => 'Strength coach', 'rating_avg' => 4.2, 'rating_count' => 3]);
    [, , $featured] = coachWithProfile($this, ['is_featured' => true, 'rating_avg' => 3.9, 'rating_count' => 2]);
    [, , $paris] = coachWithProfile($this, ['city' => 'Paris', 'country' => 'FR', 'languages' => ['fr']]);
    [, , $hidden] = coachWithProfile($this, ['is_published' => false]);

    $all = $this->getJson('/api/v1/public/coaches')->assertOk()->json('data');
    expect(array_column($all, 'slug'))->toBe([$featured->slug, $doha->slug, $paris->slug]);

    $this->getJson('/api/v1/public/coaches?city=doha')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/public/coaches?city=Paris')->assertOk()->assertJsonPath('data.0.slug', $paris->slug)->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/public/coaches?language=fr')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', $paris->slug);
    $this->getJson('/api/v1/public/coaches?language=ar&city=Doha&rating=4')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', $doha->slug);
    $this->getJson('/api/v1/public/coaches?city=Doha&mode=in_person')->assertOk()->assertJsonCount(0, 'data');
    expect(collect($all)->pluck('slug'))->not->toContain($hidden->slug);
});

it('filters by specialty, price and gender', function () {
    [, , $a] = coachWithProfile($this, ['specialties' => ['hyrox', 'running'], 'price_from_minor' => 30000, 'gender' => 'female']);
    [, , $b] = coachWithProfile($this, ['specialties' => ['strength'], 'price_from_minor' => 90000, 'gender' => 'male']);

    $this->getJson('/api/v1/public/coaches?specialty=hyrox')->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', $a->slug);
    $this->getJson('/api/v1/public/coaches?price_max=50000')->assertJsonCount(1, 'data')->assertJsonPath('data.0.price_from.amount_minor', 30000)->assertJsonPath('data.0.price_from.currency', 'QAR');
    $this->getJson('/api/v1/public/coaches?gender=male')->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', $b->slug);
});

it('shows a published profile with packages and only approved verified reviews', function () {
    [$tenant, , $profile] = coachWithProfile($this, ['bio' => ['en' => 'Ten years coaching.', 'ar' => 'عشر سنوات من التدريب.']]);
    $c1 = $this->addClient($tenant, null, ['name' => 'Layla Hassan']);
    $c2 = $this->addClient($tenant);
    $c3 = $this->addClient($tenant);
    $this->inTenant($tenant, function () use ($profile, $c1, $c2, $c3) {
        Review::create(['coach_profile_id' => $profile->id, 'client_id' => $c1->id, 'rating' => 5, 'body' => 'Life changing', 'status' => 'approved', 'is_verified' => true]);
        Review::create(['coach_profile_id' => $profile->id, 'client_id' => $c2->id, 'rating' => 1, 'body' => 'Pending one', 'status' => 'pending', 'is_verified' => true]);
        Review::create(['coach_profile_id' => $profile->id, 'client_id' => $c3->id, 'rating' => 2, 'body' => 'Unverified', 'status' => 'approved', 'is_verified' => false]);
        $product = Product::create(['type' => 'coaching_package', 'slug' => '12-weeks', 'name' => ['en' => '12-week transformation', 'ar' => 'برنامج التحول ١٢ أسبوعًا'], 'is_active' => true]);
        Price::create(['product_id' => $product->id, 'type' => 'one_time', 'amount_minor' => 199900, 'currency' => 'QAR', 'is_active' => true]);
        Product::create(['type' => 'coaching_package', 'slug' => 'old', 'name' => ['en' => 'Retired'], 'is_active' => false]);
    });

    $response = $this->withHeader('Accept-Language', 'ar')->getJson("/api/v1/public/coaches/{$profile->slug}")->assertOk();

    $response->assertJsonPath('data.bio', 'عشر سنوات من التدريب.')
        ->assertJsonCount(1, 'data.reviews')
        ->assertJsonPath('data.reviews.0.body', 'Life changing')
        ->assertJsonPath('data.reviews.0.author', 'Layla H.')
        ->assertJsonCount(1, 'data.packages')
        ->assertJsonPath('data.packages.0.prices.0.amount.amount_minor', 199900)
        ->assertJsonPath('data.rating_avg', 5)
        ->assertJsonPath('data.rating_count', 1)
        ->assertJsonStructure(['data' => ['availability' => ['booking_enabled', 'timezone', 'next_slots']]]);
});

it('returns 404 for unpublished profiles and deleted businesses', function () {
    [, , $draft] = coachWithProfile($this, ['is_published' => false]);
    [$gone, , $goneProfile] = coachWithProfile($this);
    Tenant::query()->whereKey($gone->id)->first()->delete();

    expectApiError($this->getJson("/api/v1/public/coaches/{$draft->slug}"), 404, 'not_found');
    expectApiError($this->getJson("/api/v1/public/coaches/{$goneProfile->slug}"), 404, 'not_found');
    expectApiError($this->getJson("/api/v1/public/coaches/{$draft->slug}/slots"), 404, 'not_found');
});

it('lists bookable slots from the coach availability', function () {
    [$tenant, $owner, $profile] = coachWithProfile($this, [], ['timezone' => 'Asia/Qatar']);
    $date = now('Asia/Qatar')->addDays(3);
    $this->inTenant($tenant, fn () => AvailabilityRule::create([
        'coach_id' => $owner->id, 'weekday' => $date->dayOfWeekIso, 'starts_at' => '09:00:00', 'ends_at' => '12:00:00', 'timezone' => 'Asia/Qatar',
    ]));

    $slots = $this->getJson("/api/v1/public/coaches/{$profile->slug}/slots?from={$date->toDateString()}&days=1")->assertOk()->json('data');

    expect($slots)->toHaveCount(3)
        ->and($slots[0]['starts_at'])->toBe($date->copy()->setTime(9, 0)->utc()->toIso8601String());
});

it('serves the same directory to signed-in app users', function () {
    coachWithProfile($this);
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAsIn($owner)->getJson('/api/v1/marketplace/coaches')->assertOk()->assertJsonCount(1, 'data');
});
