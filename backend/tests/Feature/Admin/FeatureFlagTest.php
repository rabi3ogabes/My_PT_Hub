<?php

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Services\PlanGate;
use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\FeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\EditFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\ListFeatureFlags;
use App\Filament\Resources\SaasPlans\Pages\EditSaasPlan;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_super_admin' => true])->save();
    [$this->tenant] = $this->createCoachTenant();
    $this->gate = app(PlanGate::class);
});

it('turns a beta flag on and off for PlanGate::feature from the admin', function () {
    $this->actingAs($this->admin);
    expect($this->gate->feature($this->tenant, 'voice_notes_beta'))->toBeFalse();

    Livewire::test(CreateFeatureFlag::class)
        ->fillForm(['key' => 'voice_notes_beta', 'enabled' => true, 'rollout' => 100])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($this->gate->feature($this->tenant, 'voice_notes_beta'))->toBeTrue();

    $flag = FeatureFlag::query()->where('key', 'voice_notes_beta')->sole();
    Livewire::test(EditFeatureFlag::class, ['record' => $flag->id])
        ->fillForm(['enabled' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    // Cache is flushed on save: the kill switch applies immediately.
    expect($this->gate->feature($this->tenant, 'voice_notes_beta'))->toBeFalse()
        ->and(ActivityLog::query()->where('event', 'admin.feature_flag.updated')->exists())->toBeTrue();
});

it('applies the table toggle, tenant allow-list and plan restriction', function () {
    $this->actingAs($this->admin);
    $flag = FeatureFlag::query()->create(['key' => 'ai_copilot', 'enabled' => true, 'plans' => ['enterprise'], 'rollout' => 100]);
    $this->tenant->forceFill(['plan' => 'pro'])->save();

    expect($this->gate->feature($this->tenant, 'ai_copilot'))->toBeFalse();

    Livewire::test(EditFeatureFlag::class, ['record' => $flag->id])
        ->fillForm(['tenants' => [$this->tenant->id]])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($this->gate->feature($this->tenant, 'ai_copilot'))->toBeTrue();

    Livewire::test(ListFeatureFlags::class)->call('updateTableColumnState', 'enabled', $flag->id, false);
    expect($flag->refresh()->enabled)->toBeFalse()
        ->and($this->gate->feature($this->tenant, 'ai_copilot'))->toBeFalse();
});

it('applies plan feature edits immediately', function () {
    $this->actingAs($this->admin);
    $this->tenant->forceFill(['plan' => 'free'])->save();
    $free = SaasPlan::query()->where('code', 'free')->sole();
    expect($this->gate->feature($this->tenant, 'ai_copilot'))->toBeFalse();

    Livewire::test(EditSaasPlan::class, ['record' => $free->id])
        ->fillForm(['features' => array_merge($free->features ?? [], ['ai_copilot'])])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->gate->feature($this->tenant, 'ai_copilot'))->toBeTrue();
});
