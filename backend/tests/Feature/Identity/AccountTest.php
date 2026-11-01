<?php

use App\Domain\Identity\Jobs\DeleteAccount;
use App\Domain\Identity\Jobs\ExportUserData;
use App\Domain\Identity\Models\User;
use App\Domain\Progress\Models\BodyMetric;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

it('exports a client\'s data as a zip and emails a signed link', function () {
    Storage::fake('local');
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner);
    $this->inTenant($tenant, fn () => BodyMetric::create(['client_id' => $client->id, 'measured_on' => '2026-10-01', 'weight_kg' => 72.4]));

    (new ExportUserData($client->id))->handle();

    $files = Storage::disk('local')->allFiles('exports/'.$client->id);
    expect($files)->toHaveCount(1);
    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($files[0]));
    expect($zip->getFromName('data/body_metrics.json'))->toContain('72.4');
    Notification::assertSentTo($client, \App\Notifications\GenericNotification::class);
});

it('anonymises and erases personal data on account deletion', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner, ['password' => 'Secret123']);
    $this->inTenant($tenant, fn () => BodyMetric::create(['client_id' => $client->id, 'measured_on' => '2026-10-01', 'weight_kg' => 70]));

    \Illuminate\Support\Facades\Bus::fake([DeleteAccount::class]);
    $this->actingAsIn($client, $tenant)->deleteJson('/api/v1/me', ['password' => 'Secret123', 'confirm' => true])->assertOk();
    \Illuminate\Support\Facades\Bus::assertDispatched(DeleteAccount::class);

    (new DeleteAccount($client->id))->handle();
    $fresh = User::withTrashed()->find($client->id);
    expect($fresh->email)->toStartWith('deleted+')->and($fresh->trashed())->toBeTrue()
        ->and(BodyMetric::withoutGlobalScopes()->where('client_id', $client->id)->count())->toBe(0);
});

it('requires owners to transfer a business with members before deleting', function () {
    [$tenant, $owner] = $this->createCoachTenant(['password' => 'Secret123']);
    $this->addClient($tenant, $owner);
    expectApiError($this->actingAsIn($owner, $tenant)->deleteJson('/api/v1/me', ['password' => 'Secret123', 'confirm' => true]), 409, 'transfer_ownership_first');
});
