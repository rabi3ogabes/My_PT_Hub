<?php

use App\Domain\Crm\Models\Contract;
use App\Domain\Crm\Services\ContractService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Notification::fake();
    Storage::fake('local');
    $this->travelTo(now()->setDate(2026, 11, 1)->setTime(9, 0));
    [$this->tenant, $this->owner] = $this->createCoachTenant([], ['name' => 'Sara Strength']);
    $this->client = $this->addClient($this->tenant, $this->owner, ['name' => 'Omar Haddad']);
});

it('manages contract templates', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $id = $this->postJson('/api/v1/contract-templates', ['name' => 'Liability waiver', 'kind' => 'waiver', 'body' => 'I, {client_name}, release {business_name}.'])
        ->assertCreated()->assertJsonPath('data.kind', 'waiver')->json('data.id');
    $this->patchJson("/api/v1/contract-templates/{$id}", ['name' => 'Waiver v2'])->assertOk()->assertJsonPath('data.name', 'Waiver v2');
    $this->getJson('/api/v1/contract-templates')->assertOk()->assertJsonCount(1, 'data');
    $this->deleteJson("/api/v1/contract-templates/{$id}")->assertOk();
    $this->getJson('/api/v1/contract-templates')->assertJsonCount(0, 'data');
});

it('sends a rendered contract and the client signs it with a verifiable audit trail', function () {
    $template = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/contract-templates', [
        'name' => 'Coaching agreement', 'body' => "Agreement between {business_name} and {client_name}, dated {date}.",
    ])->json('data.id');

    $contractId = $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/contracts', ['client_id' => $this->client->id, 'contract_template_id' => $template])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Agreement between Sara Strength and Omar Haddad, dated 2026-11-01.')
        ->assertJsonPath('data.status', 'sent')
        ->json('data.id');

    $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/contracts')->assertOk()->assertJsonPath('data.0.id', $contractId)->assertJsonPath('data.0.pdf_url', null);

    $signature = 'data:image/png;base64,'.base64_encode('fake-png-bytes');
    $this->actingAsIn($this->client, $this->tenant)
        ->withHeaders(['User-Agent' => 'AurumClient/2.1 (iPhone)'])
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
        ->postJson("/api/v1/me/contracts/{$contractId}/sign", ['signature_type' => 'draw', 'signature_data' => $signature, 'agree' => true])
        ->assertOk()->assertJsonPath('data.status', 'signed')->assertJsonPath('data.signature_type', 'draw');

    $contract = Contract::withoutGlobalScopes()->findOrFail($contractId);
    expect($contract->document_hash)->toBe(hash('sha256', 'Agreement between Sara Strength and Omar Haddad, dated 2026-11-01.'.$signature))
        ->and($contract->document_hash)->toBe(ContractService::hash($contract->rendered_body, $contract->signature_data))
        ->and($contract->signed_ip)->toBe('203.0.113.7')
        ->and($contract->signed_user_agent)->toBe('AurumClient/2.1 (iPhone)')
        ->and($contract->signed_at->toIso8601String())->toBe(now()->toIso8601String())
        ->and($contract->pdf_path)->not->toBeNull();
    Storage::disk('local')->assertExists($contract->pdf_path);

    // Tampering with the stored body breaks the fingerprint.
    expect(ContractService::hash($contract->rendered_body.' ', $contract->signature_data))->not->toBe($contract->document_hash);

    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/contracts/{$contractId}/sign", ['signature_type' => 'type', 'signature_data' => 'Omar', 'agree' => true]), 409, 'contract_not_signable');

    $pdf = $this->actingAsIn($this->owner, $this->tenant)->get("/api/v1/contracts/{$contractId}/pdf")->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($pdf->getContent(), 0, 4))->toBe('%PDF');
    $this->actingAsIn($this->owner, $this->tenant)->getJson("/api/v1/contracts/{$contractId}")->assertOk()->assertJsonPath('data.document_hash', $contract->document_hash);
});

it('accepts typed signatures and blocks signing someone else\'s contract', function () {
    $contract = $this->inTenant($this->tenant, fn () => app(ContractService::class)->send($this->tenant, $this->client, $this->owner, null, 'PAR-Q', 'عقد بين {business_name} و{client_name}'));
    $intruder = $this->addClient($this->tenant, $this->owner);

    $this->actingAsIn($intruder, $this->tenant)->postJson("/api/v1/me/contracts/{$contract->id}/sign", ['signature_type' => 'type', 'signature_data' => 'X', 'agree' => true])->assertNotFound();
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/contracts/{$contract->id}/sign", ['signature_type' => 'draw', 'signature_data' => 'not-an-image', 'agree' => true]), 422, 'validation_failed');
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/contracts/{$contract->id}/sign", ['signature_type' => 'type', 'signature_data' => 'Omar Haddad']), 422, 'validation_failed');

    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/me/contracts/{$contract->id}/sign", ['signature_type' => 'type', 'signature_data' => '  Omar Haddad ', 'agree' => true])->assertOk();
    $contract->refresh();
    expect($contract->rendered_body)->toBe('عقد بين Sara Strength وOmar Haddad')
        ->and($contract->signature_data)->toBe('Omar Haddad')
        ->and($contract->document_hash)->toBe(hash('sha256', $contract->rendered_body.'Omar Haddad'));
});

it('does not send contracts to non-clients or across tenants', function () {
    [$other, $otherOwner] = $this->createCoachTenant();
    expectApiError($this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/contracts', ['client_id' => $otherOwner->id, 'body' => 'Hi {client_name}']), 422, 'validation_failed');

    $contract = $this->inTenant($this->tenant, fn () => app(ContractService::class)->send($this->tenant, $this->client, $this->owner, null, null, 'Body'));
    $this->actingAsIn($otherOwner, $other)->getJson("/api/v1/contracts/{$contract->id}")->assertNotFound();
});
