<?php

use App\Domain\Ai\Models\AiOutput;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    $this->seedExercises();
    [$this->tenant, $this->coach] = $this->proTenant();
});

function draftContent($test): string
{
    return $test->postJson('/api/v1/ai/content', ['type' => 'program_description', 'topic' => 'Ramadan strength', 'language' => 'fr'])->assertCreated()->json('data.id');
}

it('lists, shows, edits-and-approves and rejects drafts', function () {
    $this->actingAsIn($this->coach, $this->tenant);
    $first = draftContent($this);
    $second = draftContent($this);

    $this->getJson('/api/v1/ai/outputs?filter[feature]=content&filter[status]=draft')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson("/api/v1/ai/outputs/{$first}")->assertOk()->assertJsonPath('data.payload.language', 'fr');

    $edited = AiOutput::query()->withoutGlobalScopes()->find($first)->payload;
    $edited['body'] = 'Texte retravaillé par le coach.';
    $this->postJson("/api/v1/ai/outputs/{$first}/approve", ['edited_payload' => $edited])
        ->assertOk()
        ->assertJsonPath('data.status', 'edited')
        ->assertJsonPath('data.edited_payload.body', 'Texte retravaillé par le coach.')
        ->assertJsonPath('data.edited_diff.0.path', 'body')
        ->assertJsonPath('data.reviewed_by.id', $this->coach->id);

    $this->postJson("/api/v1/ai/outputs/{$second}/reject", ['note' => 'Off brand'])
        ->assertOk()->assertJsonPath('data.status', 'rejected')->assertJsonPath('data.review_note', 'Off brand');

    expectApiError($this->postJson("/api/v1/ai/outputs/{$second}/approve"), 409, 'ai_output_closed');
    expectApiError($this->postJson("/api/v1/ai/outputs/{$first}/approve", ['apply' => true]), 422, 'not_applicable');
});

it('validates edited program payloads before applying', function () {
    $this->actingAsIn($this->coach, $this->tenant);
    $id = $this->postJson('/api/v1/ai/programs', ['prompt' => '3 day full body'])->assertCreated()->json('data.id');
    $payload = AiOutput::query()->withoutGlobalScopes()->find($id)->payload;
    $payload['days'][0]['workout']['blocks'][0]['exercises'][0]['exercise_id'] = 'made-up';

    $response = $this->postJson("/api/v1/ai/outputs/{$id}/approve", ['edited_payload' => $payload, 'apply' => true]);
    expectApiError($response, 422, 'validation_failed');
    expect(AiOutput::query()->withoutGlobalScopes()->find($id)->status)->toBe('draft');
});

it('never exposes the copilot review queue to clients', function () {
    $client = $this->addClient($this->tenant, $this->coach);
    $this->actingAsIn($this->coach, $this->tenant);
    $id = draftContent($this);

    $this->actingAsIn($client, $this->tenant);
    expectApiError($this->getJson('/api/v1/ai/outputs'), 403, 'forbidden');
    expectApiError($this->getJson("/api/v1/ai/outputs/{$id}"), 403, 'forbidden');
    expectApiError($this->postJson("/api/v1/ai/outputs/{$id}/approve"), 403, 'forbidden');
    expectApiError($this->postJson('/api/v1/ai/programs', ['prompt' => '3 days']), 403, 'forbidden');
});

it('isolates AI drafts between tenants', function () {
    $this->actingAsIn($this->coach, $this->tenant);
    $id = draftContent($this);

    [$other, $otherOwner] = $this->proTenant();
    $this->actingAsIn($otherOwner, $other);
    $this->getJson('/api/v1/ai/outputs')->assertOk()->assertJsonCount(0, 'data');
    expectApiError($this->getJson("/api/v1/ai/outputs/{$id}"), 404, 'not_found');
    expectApiError($this->postJson("/api/v1/ai/outputs/{$id}/approve"), 404, 'not_found');
    expectApiError($this->postJson("/api/v1/ai/outputs/{$id}/reject"), 404, 'not_found');

    expect(AiOutput::query()->withoutGlobalScopes()->find($id)->tenant_id)->toBe($this->tenant->id);
});
