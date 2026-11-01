<?php

use Illuminate\Support\Facades\Http;
use App\Domain\Identity\Models\User;
use App\Domain\Marketing\Models\Review;
use App\Domain\Marketing\Models\ReviewRequest;
use App\Domain\Marketing\Services\ReviewService;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Notifications\Marketplace\ReviewRequestNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Http::preventStrayRequests();
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->profile = CoachProfile::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->firstOrFail();
});

it('lets the owner edit and publish the profile with translatable bio', function () {
    $this->profile->forceFill(['certifications' => [['name' => 'NASM CPT', 'issuer' => 'NASM', 'year' => 2019, 'verified' => true]]])->save();

    $this->actingAsIn($this->owner, $this->tenant)->putJson('/api/v1/coach-profile?lang=fr', [
        'slug' => 'sara-strength',
        'headline' => 'Strength & HYROX coach',
        'bio' => ['en' => 'I help busy professionals get strong.', 'ar' => 'أساعد المهنيين المشغولين على اكتساب القوة.', 'fr' => 'J’aide les actifs à devenir forts.', 'de' => 'ignored'],
        'specialties' => ['Strength', 'HYROX', 'Fat Loss'],
        'languages' => ['ar', 'en', 'fr'],
        'certifications' => [['name' => 'NASM CPT', 'issuer' => 'NASM', 'year' => 2019], ['name' => 'Precision Nutrition L1', 'verified' => true]],
        'city' => 'Doha', 'country' => 'qa', 'offers_in_person' => true, 'price_from_minor' => 35000,
        'instagram' => '@sara.strength',
        'is_published' => true,
        'is_verified' => true,
        'is_featured' => true,
    ])->assertOk()
        ->assertJsonPath('data.slug', 'sara-strength')
        ->assertJsonPath('data.bio', 'J’aide les actifs à devenir forts.')
        ->assertJsonPath('data.bio_translations.ar', 'أساعد المهنيين المشغولين على اكتساب القوة.')
        ->assertJsonMissingPath('data.bio_translations.de')
        ->assertJsonPath('data.specialties', ['strength', 'hyrox', 'fat_loss'])
        ->assertJsonPath('data.certifications.0.verified', true)
        ->assertJsonPath('data.certifications.1.verified', false)
        ->assertJsonPath('data.country', 'QA')
        ->assertJsonPath('data.instagram', 'sara.strength')
        ->assertJsonPath('data.is_published', true)
        ->assertJsonPath('data.is_verified', false)
        ->assertJsonPath('data.is_featured', false);

    // A plain string bio is stored under the request locale only.
    $this->actingAsIn($this->owner, $this->tenant)->putJson('/api/v1/coach-profile?lang=es', ['bio' => 'Ayudo a profesionales.'])
        ->assertOk()->assertJsonPath('data.bio_translations.es', 'Ayudo a profesionales.')->assertJsonPath('data.bio_translations.en', 'I help busy professionals get strong.');

    $this->getJson('/api/v1/public/coaches/sara-strength')->assertOk()->assertJsonPath('data.headline', 'Strength & HYROX coach');
});

it('enforces unique slugs and owner-only access', function () {
    [$other, $otherOwner] = $this->createCoachTenant();
    $otherSlug = CoachProfile::withoutGlobalScopes()->where('tenant_id', $other->id)->value('slug');

    $this->actingAsIn($this->owner, $this->tenant)->putJson('/api/v1/coach-profile', ['slug' => $otherSlug])->assertStatus(422);

    $coach = $this->addStaff($this->tenant, 'coach');
    expectApiError($this->actingAsIn($coach, $this->tenant)->getJson('/api/v1/coach-profile'), 403, 'forbidden');
});

it('counts only approved verified reviews in the aggregate rating', function () {
    $clients = collect(range(1, 4))->map(fn () => $this->addClient($this->tenant, $this->owner));
    $outsider = User::factory()->create();
    $reviews = app(ReviewService::class);

    $r1 = $reviews->submit($this->tenant, $clients[0], 5, 'Brilliant');
    $r2 = $reviews->submit($this->tenant, $clients[1], 3, 'Good');
    $reviews->submit($this->tenant, $clients[2], 1, 'Spam?');
    $unverified = $reviews->submit($this->tenant, $outsider, 1, 'Never trained here');

    expect($r1->status)->toBe('pending')->and($r1->is_verified)->toBeTrue()->and($unverified->is_verified)->toBeFalse();
    expect($this->profile->refresh()->rating_count)->toBe(0);

    $reviews->moderate($r1, 'approved');
    $reviews->moderate($r2, 'approved');
    $reviews->moderate($unverified, 'approved');
    $this->profile->refresh();
    expect($this->profile->rating_count)->toBe(2)->and($this->profile->rating_avg)->toEqual(4.0);

    $reviews->moderate($r2, 'rejected');
    expect($this->profile->refresh()->rating_avg)->toEqual(5.0);

    $r1->delete();
    expect($this->profile->refresh()->rating_count)->toBe(0);
});

it('lets the owner reply to a review of their business but not another tenant\'s', function () {
    $client = $this->addClient($this->tenant, $this->owner);
    $review = app(ReviewService::class)->submit($this->tenant, $client, 4, 'Great sessions');

    $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/reviews/{$review->id}/reply", ['reply' => 'Thank you, keep it up!'])
        ->assertOk()->assertJsonPath('data.coach_reply', 'Thank you, keep it up!');
    expect($review->refresh()->replied_at)->not->toBeNull();

    [$other, $otherOwner] = $this->createCoachTenant();
    expectApiError($this->actingAsIn($otherOwner, $other)->postJson("/api/v1/reviews/{$review->id}/reply", ['reply' => 'Hijack']), 404, 'not_found');
    expect($review->refresh()->coach_reply)->toBe('Thank you, keep it up!');
});

it('requests a review 30 days after a client joins, once, with a working signed link', function () {
    Notification::fake();
    $this->profile->forceFill(['is_published' => true])->save();
    $due = $this->addClient($this->tenant, $this->owner);
    $fresh = $this->addClient($this->tenant, $this->owner);
    Membership::withoutGlobalScopes()->where('user_id', $due->id)->update(['joined_at' => now()->subDays(31)]);
    Membership::withoutGlobalScopes()->where('user_id', $fresh->id)->update(['joined_at' => now()->subDays(5)]);

    expect(app(ReviewService::class)->sendDueRequests())->toBe(1)
        ->and(app(ReviewService::class)->sendDueRequests())->toBe(0);

    Notification::assertSentTo($due, ReviewRequestNotification::class);
    Notification::assertNotSentTo($fresh, ReviewRequestNotification::class);
    expect(ReviewRequest::withoutGlobalScopes()->where('client_id', $due->id)->count())->toBe(1);

    $url = app(ReviewService::class)->submitUrl($this->tenant, $due);
    $this->postJson($url, ['rating' => 5, 'body' => 'Best coach in Doha'])->assertCreated()->assertJsonPath('data.status', 'pending');
    $this->postJson($url.'x', ['rating' => 5])->assertForbidden();
    $this->postJson(URL::route('marketplace.reviews.submit', ['tenant' => $this->tenant->id, 'client' => $fresh->id]), ['rating' => 1])->assertForbidden();

    expect(Review::withoutGlobalScopes()->where('client_id', $due->id)->value('is_verified'))->toBeTrue();
});
