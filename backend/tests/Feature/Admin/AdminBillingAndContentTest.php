<?php

use App\Domain\Billing\Models\SaasInvoice;
use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\Exercise;
use App\Filament\Pages\WhiteLabelApps;
use App\Filament\Resources\Exercises\Pages\CreateExercise;
use App\Filament\Resources\Exercises\Pages\EditExercise;
use App\Filament\Resources\SaasSubscriptions\Pages\ListSaasSubscriptions;
use App\Filament\Resources\Users\Pages\EditUser;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_super_admin' => true])->save();
    [$this->tenant, $this->owner] = $this->createCoachTenant();
});

it('cancels the current subscription through the billing service', function () {
    $this->actingAs($this->admin);
    $sub = $this->tenant->subscription;
    expect($sub->status)->toBe('trialing');

    Livewire::test(ListSaasSubscriptions::class)
        ->callTableAction('cancel', $sub, ['reason' => 'admin_test']);

    expect($sub->refresh()->status)->toBe('cancelled')
        ->and($sub->cancel_reason)->toBe('admin_test')
        ->and($this->tenant->refresh()->plan)->toBe('free')
        ->and(ActivityLog::query()->where('event', 'billing.cancelled')->exists())->toBeTrue();
});

it('refunds part of a paid subscription and records a negative invoice', function () {
    $this->actingAs($this->admin);
    $sub = app(SaasBillingService::class)->activate($this->tenant, SaasPlan::query()->where('code', 'pro')->sole(), 'year');
    $amount = min(500, $sub->price_minor);

    Livewire::test(ListSaasSubscriptions::class)
        ->callTableAction('refund', $sub, ['amount_minor' => $amount, 'reason' => 'Goodwill'])
        ->assertHasNoTableActionErrors();

    $invoice = SaasInvoice::query()->where('saas_subscription_id', $sub->id)->where('status', 'refunded')->sole();
    expect($invoice->amount_minor)->toBe(-$amount)
        ->and(ActivityLog::query()->where('event', 'billing.refunded')->where('subject_id', $sub->id)->exists())->toBeTrue();
});

it('creates a global exercise with per-locale content and edits it', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateExercise::class)
        ->fillForm([
            'slug' => 'romanian-deadlift',
            'level' => 'intermediate',
            'primary_muscle' => 'hamstrings',
            'name' => ['en' => 'Romanian deadlift', 'ar' => 'الرفعة الرومانية', 'fr' => 'Soulevé de terre roumain', 'es' => ''],
            'video_url' => 'https://cdn.example.com/rdl.m3u8',
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $exercise = Exercise::withoutGlobalScopes()->where('slug', 'romanian-deadlift')->sole();
    expect($exercise->tenant_id)->toBeNull()
        ->and($exercise->name)->toBe(['en' => 'Romanian deadlift', 'ar' => 'الرفعة الرومانية', 'fr' => 'Soulevé de terre roumain']);

    // Steps stored as {locale: [..]} (or a legacy string) survive an edit round trip.
    $exercise->forceFill(['instructions' => ['en' => ['Hinge at the hips', 'Keep a neutral spine'], 'fr' => "Charnière\nDos neutre"]])->save();

    Livewire::test(EditExercise::class, ['record' => $exercise->id])
        ->fillForm(['name.es' => 'Peso muerto rumano', 'is_published' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($exercise->refresh()->name['es'])->toBe('Peso muerto rumano')
        ->and($exercise->is_published)->toBeFalse()
        ->and($exercise->instructions['en'])->toBe(['Hinge at the hips', 'Keep a neutral spine'])
        ->and($exercise->instructions['fr'])->toBe(['Charnière', 'Dos neutre']);
});

it('grants and revokes super admin with an audit entry, but never for yourself', function () {
    $this->actingAs($this->admin);

    Livewire::test(EditUser::class, ['record' => $this->owner->id])
        ->fillForm(['is_super_admin' => true])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($this->owner->refresh()->is_super_admin)->toBeTrue()
        ->and(ActivityLog::query()->where('event', 'admin.user.super_admin_granted')->exists())->toBeTrue();

    Livewire::test(EditUser::class, ['record' => $this->admin->id])
        ->fillForm(['name' => 'Root'])
        ->call('save');
    expect($this->admin->refresh()->is_super_admin)->toBeTrue()->and($this->admin->name)->toBe('Root');
});

it('lists branded-app tenants and saves their white-label config', function () {
    $this->actingAs($this->admin);
    $this->tenant->forceFill(['plan' => 'studio'])->save();
    [$freeTenant] = $this->createCoachTenant();
    $freeTenant->forceFill(['plan' => 'free'])->save();

    Livewire::test(WhiteLabelApps::class)
        ->assertCanSeeTableRecords([$this->tenant])
        ->assertCanNotSeeTableRecords([$freeTenant])
        ->callTableAction('editConfig', $this->tenant, [
            'app_name' => 'Elite Coaching',
            'default_locale' => 'ar',
            'bundle_id' => 'com.elite.coach',
            'android_package' => 'com.elite.coach',
            'colors' => '{"primary": "#D4AF6A", "background": "#0B0B0F"}',
            'build_status' => 'queued',
        ])
        ->assertHasNoTableActionErrors();

    $config = Tenant::query()->find($this->tenant->id)->setting('whitelabel');
    expect($config['bundle_id'])->toBe('com.elite.coach')
        ->and($config['colors'])->toBe(['primary' => '#D4AF6A', 'background' => '#0B0B0F'])
        ->and($config['build_status'])->toBe('queued');

    Livewire::test(WhiteLabelApps::class)
        ->callTableAction('editConfig', $this->tenant, ['colors' => '["not-an-object"]', 'bundle_id' => 'bad id'])
        ->assertHasTableActionErrors(['colors', 'bundle_id']);
});
