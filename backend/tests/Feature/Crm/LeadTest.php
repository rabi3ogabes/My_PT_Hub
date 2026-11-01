<?php

use App\Domain\Crm\Models\Lead;
use App\Domain\Crm\Models\LeadStage;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Tenancy\Models\Invitation;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    [$this->tenant, $this->owner] = $this->createCoachTenant([], ['country' => 'MA', 'currency' => 'MAD']);
});

it('returns the default pipeline and lets the coach customise it', function () {
    $stages = $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/lead-stages')->assertOk()->json('data');
    expect(collect($stages)->pluck('kind')->all())->toBe(['open', 'open', 'open', 'open', 'won', 'lost']);

    $lead = $this->inTenant($this->tenant, fn () => Lead::create(['name' => 'Youssef', 'lead_stage_id' => $stages[1]['id']]));
    $payload = ['stages' => [
        ['id' => $stages[0]['id'], 'name' => 'Nouveau', 'kind' => 'open'],
        ['name' => 'Appel découverte', 'kind' => 'open'],
        ['id' => $stages[4]['id'], 'name' => 'Gagné', 'kind' => 'won'],
        ['id' => $stages[5]['id'], 'name' => 'Perdu', 'kind' => 'lost'],
    ]];
    $saved = $this->actingAsIn($this->owner, $this->tenant)->putJson('/api/v1/lead-stages', $payload)->assertOk()->json('data');

    expect(collect($saved)->pluck('name')->all())->toBe(['Nouveau', 'Appel découverte', 'Gagné', 'Perdu'])
        ->and(collect($saved)->pluck('position')->all())->toBe([0, 1, 2, 3])
        ->and($lead->refresh()->lead_stage_id)->toBe($stages[0]['id']) // removed stage → leads moved to the first stage
        ->and(LeadStage::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->count())->toBe(4);
});

it('creates, filters, updates and deletes leads', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $created = $this->postJson('/api/v1/leads', ['name' => 'Salma', 'email' => 'salma@example.ma', 'source' => 'instagram', 'tags' => ['ramadan'], 'value_minor' => 150000, 'consent' => true])
        ->assertCreated()
        ->assertJsonPath('data.stage.kind', 'open')
        ->assertJsonPath('data.owner.id', $this->owner->id)
        ->assertJsonPath('data.value.amount_minor', 150000)
        ->assertJsonPath('data.value.currency', 'MAD');
    $this->postJson('/api/v1/leads', ['name' => 'Karim', 'source' => 'whatsapp'])->assertCreated();

    expect($this->getJson('/api/v1/leads?filter[source]=instagram')->json('meta.total'))->toBe(1)
        ->and($this->getJson('/api/v1/leads?filter[tag]=ramadan')->json('data.0.name'))->toBe('Salma')
        ->and($this->getJson('/api/v1/leads?q=kar')->json('data.0.name'))->toBe('Karim')
        ->and($this->getJson('/api/v1/leads?sort=name')->json('data.0.name'))->toBe('Karim');

    $id = $created->json('data.id');
    $proposal = LeadStage::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->where('position', 3)->first();
    $this->patchJson("/api/v1/leads/{$id}", ['lead_stage_id' => $proposal->id, 'phone' => '+212600000000'])
        ->assertOk()->assertJsonPath('data.stage.id', $proposal->id)->assertJsonPath('data.activities.0.type', 'stage');
    expect($this->getJson("/api/v1/leads?filter[stage]={$proposal->id}")->json('meta.total'))->toBe(1);

    $this->deleteJson("/api/v1/leads/{$id}")->assertOk();
    expect($this->getJson('/api/v1/leads')->json('meta.total'))->toBe(1);
    expectApiError($this->postJson('/api/v1/leads', ['name' => 'X', 'lead_stage_id' => '01HZZZZZZZZZZZZZZZZZZZZZZZ']), 422, 'validation_failed');
});

it('logs notes, tasks and stage changes as activities', function () {
    $lead = $this->inTenant($this->tenant, fn () => Lead::create(['name' => 'Nadia']));
    $won = LeadStage::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->where('kind', 'won')->first();
    $this->actingAsIn($this->owner, $this->tenant);

    $this->postJson("/api/v1/leads/{$lead->id}/activities", ['type' => 'note', 'body' => 'Wants to lose 8 kg before summer'])->assertCreated()->assertJsonPath('data.type', 'note')->assertJsonPath('data.user.id', $this->owner->id);
    $this->postJson("/api/v1/leads/{$lead->id}/activities", ['type' => 'task', 'body' => 'Call back', 'due_at' => now()->addDay()->toIso8601String()])->assertCreated()->assertJsonPath('data.completed_at', null);
    $this->postJson("/api/v1/leads/{$lead->id}/activities", ['type' => 'stage', 'lead_stage_id' => $won->id])->assertCreated()->assertJsonPath('data.type', 'stage');

    expect($lead->refresh()->lead_stage_id)->toBe($won->id);
    $this->getJson("/api/v1/leads/{$lead->id}")->assertOk()->assertJsonCount(3, 'data.activities');
});

it('converts a lead into an invited client with an optional checkout link', function () {
    $lead = $this->inTenant($this->tenant, fn () => Lead::create(['name' => 'Omar Lead', 'email' => 'omar.lead@example.com']));
    $product = $this->inTenant($this->tenant, function () {
        $p = Product::create(['type' => 'coaching_package', 'slug' => 'online-12', 'name' => ['en' => 'Online 12 weeks'], 'is_active' => true]);
        Price::create(['product_id' => $p->id, 'amount_minor' => 120000, 'currency' => 'MAD']);

        return $p;
    });

    $res = $this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/leads/{$lead->id}/convert", ['product_id' => $product->id])->assertOk();
    $code = $res->json('data.invitation_code');
    expect($res->json('data.checkout_url'))->toStartWith(url("checkout/{$this->tenant->slug}/online-12").'?')
        ->and($res->json('data.checkout_url'))->toContain('email=omar.lead%40example.com')
        ->and($res->json('data.lead.stage.kind'))->toBe('won')
        ->and($res->json('data.lead.converted_at'))->not->toBeNull()
        ->and(Invitation::withoutGlobalScopes()->where('code', $code)->where('tenant_id', $this->tenant->id)->value('email'))->toBe('omar.lead@example.com');

    $noEmail = $this->inTenant($this->tenant, fn () => Lead::create(['name' => 'No email']));
    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson("/api/v1/leads/{$noEmail->id}/convert"), 422, 'validation_failed');
});

it('isolates leads between tenants and requires crm.manage', function () {
    [$other, $otherOwner] = $this->createCoachTenant();
    $lead = $this->inTenant($this->tenant, fn () => Lead::create(['name' => 'Private']));

    $this->actingAsIn($otherOwner, $other)->getJson("/api/v1/leads/{$lead->id}")->assertNotFound();
    $this->actingAsIn($otherOwner, $other)->postJson("/api/v1/leads/{$lead->id}/convert")->assertNotFound();
    expect($this->actingAsIn($otherOwner, $other)->getJson('/api/v1/leads')->json('meta.total'))->toBe(0);

    $assistant = $this->addStaff($this->tenant, 'assistant');
    expectApiError($this->actingAsIn($assistant, $this->tenant)->getJson('/api/v1/leads'), 403, 'forbidden');
});
