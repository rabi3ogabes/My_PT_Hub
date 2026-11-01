<?php

use App\Domain\Crm\Models\Lead;
use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Models\WebhookEndpoint;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Notifications\Marketplace\NewMarketplaceLead;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Http::preventStrayRequests();
});

function publishedCoach($test, array $profile = []): array
{
    [$tenant, $owner] = (fn () => $this->createCoachTenant([], ['currency' => 'QAR']))->call($test);
    $model = CoachProfile::withoutGlobalScopes()->where('tenant_id', $tenant->id)->firstOrFail();
    $model->forceFill(array_merge(['is_published' => true, 'city' => 'Doha', 'languages' => ['en']], $profile))->save();

    return [$tenant, $owner, $model->refresh()];
}

it('creates a consented CRM lead in the coach\'s tenant and notifies the coach', function () {
    Notification::fake();
    [$tenant, $owner, $profile] = publishedCoach($this);
    [$otherTenant] = publishedCoach($this);

    $this->postJson("/api/v1/public/coaches/{$profile->slug}/leads", [
        'name' => 'Noura Al-Kuwari', 'email' => 'Noura@Example.com', 'message' => 'I want to get stronger', 'goal' => 'strength', 'consent' => true,
    ])->assertCreated()->assertJsonPath('data.received', true);

    $lead = Lead::withoutGlobalScopes()->sole();
    expect($lead->tenant_id)->toBe($tenant->id)
        ->and($lead->owner_id)->toBe($owner->id)
        ->and($lead->email)->toBe('noura@example.com')
        ->and($lead->consent)->toBeTrue()
        ->and($lead->source)->toBe('profile')
        ->and($lead->lead_stage_id)->not->toBeNull()
        ->and($lead->tags)->toContain('marketplace', 'strength');
    expect($this->inTenant($otherTenant, fn () => Lead::count()))->toBe(0)
        ->and($this->inTenant($tenant, fn () => Lead::count()))->toBe(1);

    Notification::assertSentTo($owner, NewMarketplaceLead::class, fn ($n) => $n->leadId === $lead->id && $n->tenantId === $tenant->id);

    // A repeat submission the same day updates the open lead instead of duplicating it.
    $this->postJson("/api/v1/public/coaches/{$profile->slug}/leads", ['name' => 'Noura', 'email' => 'noura@example.com', 'message' => 'Any slots this week?', 'consent' => true])->assertCreated();
    expect(Lead::withoutGlobalScopes()->count())->toBe(1)->and(Lead::withoutGlobalScopes()->first()->message)->toBe('Any slots this week?');
});

it('requires consent and contact details and traps bots', function () {
    [, , $profile] = publishedCoach($this);

    $this->postJson("/api/v1/public/coaches/{$profile->slug}/leads", ['name' => 'A', 'email' => 'a@example.com'])->assertStatus(422);
    $this->postJson("/api/v1/public/coaches/{$profile->slug}/leads", ['name' => 'A', 'consent' => true])->assertStatus(422);
    $this->postJson("/api/v1/public/coaches/{$profile->slug}/leads", ['name' => 'A', 'email' => 'a@example.com', 'consent' => true, 'website' => 'http://spam'])->assertStatus(422);
    expect(Lead::withoutGlobalScopes()->count())->toBe(0);
});

it('emits the lead.created webhook to the coach\'s endpoints only', function () {
    Http::fake(['hooks.example.com/*' => Http::response('ok')]);
    [$tenant, , $profile] = publishedCoach($this);
    [$other] = publishedCoach($this);
    $this->inTenant($tenant, fn () => WebhookEndpoint::create(['url' => 'https://hooks.example.com/mine', 'events' => ['lead.created'], 'secret' => 's', 'is_active' => true]));
    $this->inTenant($other, fn () => WebhookEndpoint::create(['url' => 'https://hooks.example.com/theirs', 'events' => ['lead.created'], 'secret' => 's', 'is_active' => true]));

    $this->postJson("/api/v1/public/coaches/{$profile->slug}/leads", ['name' => 'Sam', 'phone' => '+97455500000', 'consent' => true])->assertCreated();

    Http::assertSentCount(1);
    Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://hooks.example.com/mine' && $r['event'] === 'lead.created' && $r['data']['lead']['name'] === 'Sam');
});

it('ranks coaches from quiz answers and creates an introduction lead for the best match', function () {
    Notification::fake();
    [$bestTenant, $bestOwner, $best] = publishedCoach($this, ['specialties' => ['fat_loss', 'nutrition'], 'languages' => ['ar', 'en'], 'price_from_minor' => 40000, 'rating_avg' => 4.8, 'rating_count' => 12, 'is_verified' => true]);
    [, , $second] = publishedCoach($this, ['specialties' => ['powerlifting'], 'languages' => ['ar']]);
    [, , $excluded] = publishedCoach($this, ['specialties' => ['fat_loss'], 'languages' => ['ar'], 'offers_online' => false, 'offers_in_person' => true]);

    $client = User::factory()->create(['name' => 'Huda']);

    $data = $this->actingAsIn($client)->postJson('/api/v1/marketplace/match', [
        'goal' => 'fat_loss', 'languages' => ['ar'], 'mode' => 'online', 'budget_max_minor' => 50000, 'currency' => 'QAR', 'consent' => true,
    ])->assertOk()->json('data');

    expect(array_map(fn ($m) => $m['coach']['slug'], $data['matches']))->toBe([$best->slug, $second->slug])
        ->and($data['matches'][0]['score'])->toBeGreaterThan($data['matches'][1]['score'])
        ->and(array_column($data['matches'][0]['reasons'], 'key'))->toContain('specialty', 'language', 'online', 'budget', 'verified')
        ->and($data['introduction'])->toBe(['coach_slug' => $best->slug, 'lead_created' => true]);

    $lead = Lead::withoutGlobalScopes()->sole();
    expect($lead->tenant_id)->toBe($bestTenant->id)->and($lead->source)->toBe('marketplace')->and($lead->email)->toBe($client->email);
    Notification::assertSentTo($bestOwner, NewMarketplaceLead::class, fn ($n) => $n->origin === 'match');
    expect(collect($data['matches'])->pluck('coach.slug'))->not->toContain($excluded->slug);
});

it('requires consent for the introduction unless the client opts out of it', function () {
    publishedCoach($this, ['specialties' => ['strength']]);
    $client = User::factory()->create();

    $this->actingAsIn($client)->postJson('/api/v1/marketplace/match', ['goal' => 'strength'])->assertStatus(422);
    $this->actingAsIn($client)->postJson('/api/v1/marketplace/match', ['goal' => 'strength', 'introduce' => false])
        ->assertOk()->assertJsonPath('data.introduction', null)->assertJsonCount(1, 'data.matches');
    expect(Lead::withoutGlobalScopes()->count())->toBe(0);
});
