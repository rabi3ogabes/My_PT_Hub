<?php

use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Livewire\App\DashboardKpis;
use App\Livewire\App\Onboarding;
use App\Livewire\App\Settings\Billing;
use App\Livewire\App\Settings\Business;
use App\Livewire\App\Settings\Team;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => $this->withoutVite());

it('sends unauthenticated visitors to the login page', function () {
    $this->get('/app')->assertRedirect(route('login'));
});

it('sends a fresh owner to onboarding, then renders the dashboard', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAs($owner)->get('/app')->assertRedirect(route('app.onboarding'));
    $tenant->forceFill(['onboarded_at' => now()])->save();
    $this->actingAs($owner)->get('/app')->assertOk()->assertSee(__('web.dashboard.checklist_title'))->assertSee($tenant->name);
});

it('renders every settings page for the owner', function (string $page) {
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAs($owner)->get('/app/settings/'.$page)->assertOk();
})->with(['profile', 'business', 'team', 'billing', 'notifications']);

it('saves the onboarding wizard to the tenant', function () {
    Storage::fake('public');
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAs($owner);

    Livewire::test(Onboarding::class)
        ->set('name', 'Atlas Performance')
        ->set('logo', UploadedFile::fake()->image('logo.png', 300, 300))
        ->call('next')->assertSet('step', 2)
        ->set('brandColor', '#3DBE8B')
        ->call('next')->assertSet('step', 3)
        ->set('currency', 'QAR')->set('locale', 'ar')->set('timezone', 'Asia/Qatar')
        ->call('next')->assertSet('step', 4)->assertHasNoErrors();

    $tenant->refresh();
    expect($tenant->name)->toBe('Atlas Performance')
        ->and($tenant->brand_color)->toBe('#3DBE8B')
        ->and($tenant->currency)->toBe('QAR')
        ->and($tenant->locale)->toBe('ar')
        ->and($tenant->timezone)->toBe('Asia/Qatar')
        ->and($tenant->onboarded_at)->not->toBeNull()
        ->and($tenant->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($tenant->logo_path);
});

it('validates onboarding input', function () {
    [, $owner] = $this->createCoachTenant();
    $this->actingAs($owner);
    Livewire::test(Onboarding::class)->set('name', '')->call('next')->assertHasErrors('name')->assertSet('step', 1);
});

it('cancels the subscription in exactly two clicks', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAs($owner);
    expect($tenant->subscription->status)->toBe('trialing');

    $component = Livewire::test(Billing::class)
        ->assertSee(__('web.billing.cancel_trial'))
        ->call('askCancel')                       // click 1
        ->assertSet('confirmingCancel', true);
    expect($tenant->fresh()->subscription->status)->toBe('trialing');

    $component->set('reason', 'price')->call('cancel')   // click 2
        ->assertSet('confirmingCancel', false)
        ->assertSet('cancelled', true)
        ->assertSee(__('web.billing.cancelled_title'));

    $sub = SaasSubscription::query()->where('tenant_id', $tenant->id)->latest()->first();
    expect($sub->status)->toBe('cancelled')->and($sub->cancel_reason)->toBe('price')
        ->and($tenant->fresh()->plan)->toBe('free');
});

it('cancels a paid monthly plan at period end and can resume', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAs($owner);
    Livewire::test(Billing::class)->call('subscribe', 'pro');
    expect($tenant->fresh()->subscription->status)->toBe('active');

    Livewire::test(Billing::class)->call('askCancel')->call('cancel')->assertSet('cancelled', true);
    $sub = $tenant->fresh()->subscription;
    expect($sub->cancel_at_period_end)->toBeTrue()->and($sub->status)->toBe('active');

    Livewire::test(Billing::class)->call('resume');
    expect($tenant->fresh()->subscription->cancel_at_period_end)->toBeFalse();
});

it('does not let a coach cancel the owner subscription', function () {
    [$tenant] = $this->createCoachTenant();
    $coach = $this->addStaff($tenant, 'coach');
    $this->actingAs($coach);
    Livewire::test(Billing::class)->call('askCancel')->assertForbidden();
});

it('redirects clients away from the coach dashboard to the get-the-app page', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner);
    $this->actingAs($client)->get('/app')->assertRedirect(route('app.get-the-app'));
    $this->actingAs($client)->get('/app/settings/billing')->assertRedirect(route('app.get-the-app'));
    $this->actingAs($client)->get('/app/get-the-app')->assertOk()->assertSee(__('web.get_app.title'));
});

it('blocks clients from coach Livewire components', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $client = $this->addClient($tenant, $owner);
    $this->actingAs($client);
    Livewire::withoutLazyLoading()->test(DashboardKpis::class)->assertForbidden();
});

it('counts only the current tenant on the dashboard', function () {
    [$tenantA, $ownerA] = $this->createCoachTenant();
    [$tenantB, $ownerB] = $this->createCoachTenant();
    $a1 = $this->addClient($tenantA, $ownerA);
    $this->addClient($tenantA, $ownerA);
    foreach (range(1, 3) as $i) {
        $this->addClient($tenantB, $ownerB);
    }
    $b1 = $this->addClient($tenantB, $ownerB);
    ClientProfile::withoutGlobalScopes()->where('tenant_id', $tenantA->id)->where('user_id', $a1->id)->update(['risk_status' => 'red']);
    ClientProfile::withoutGlobalScopes()->where('tenant_id', $tenantB->id)->update(['risk_status' => 'red']);
    $formA = DB::table('checkin_forms')->where('tenant_id', $tenantA->id)->value('id');
    $formB = DB::table('checkin_forms')->where('tenant_id', $tenantB->id)->value('id');
    DB::table('checkin_responses')->insert([
        ['id' => (string) str()->ulid(), 'tenant_id' => $tenantA->id, 'checkin_form_id' => $formA, 'client_id' => $a1->id, 'status' => 'submitted', 'created_at' => now(), 'updated_at' => now()],
        ['id' => (string) str()->ulid(), 'tenant_id' => $tenantB->id, 'checkin_form_id' => $formB, 'client_id' => $b1->id, 'status' => 'submitted', 'created_at' => now(), 'updated_at' => now()],
        ['id' => (string) str()->ulid(), 'tenant_id' => $tenantB->id, 'checkin_form_id' => $formB, 'client_id' => $b1->id, 'status' => 'submitted', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($ownerA);
    $component = Livewire::withoutLazyLoading()->test(DashboardKpis::class);
    $kpis = $component->instance()->kpis();
    expect($kpis['active_clients'])->toBe(2)
        ->and($kpis['at_risk'])->toBe(1)
        ->and($kpis['checkins'])->toBe(1)
        ->and($kpis['mrr_minor'])->toBe(0);
    $component->assertSee($a1->name)->assertDontSee($b1->name);
});

it('invites a team member within plan limits', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAs($owner);
    // Pro trial allows one coach seat (the owner): inviting is blocked by PlanGate.
    Livewire::test(Team::class)->set('inviteEmail', 'coach@example.com')->set('inviteRole', 'coach')->call('invite')->assertHasErrors('inviteEmail');

    $tenant->forceFill(['plan' => 'studio'])->save();
    Livewire::test(Team::class)->set('inviteEmail', 'coach@example.com')->set('inviteRole', 'assistant')->call('invite')->assertHasNoErrors();
    $this->assertDatabaseHas('invitations', ['tenant_id' => $tenant->id, 'email' => 'coach@example.com', 'role' => 'assistant']);
});

it('saves business, legal and payment settings into the tenant json columns', function () {
    [$tenant, $owner] = $this->createCoachTenant();
    $tenant->forceFill(['settings' => array_merge($tenant->settings ?? [], ['ai' => ['keep' => true]])])->save();
    $this->actingAs($owner);
    Livewire::test(Business::class)
        ->set('name', 'Atlas Performance LLC')
        ->set('legal.legal_name', 'Atlas Performance W.L.L.')
        ->set('legal.vat_number', '300123456700003')
        ->set('legal.address', 'West Bay, Doha')
        ->set('legal.registration_number', 'CR-12345')
        ->set('taxEnabled', true)
        ->set('taxRate', '5')
        ->set('clientCanPause', true)
        ->call('save')->assertHasNoErrors();
    $t = $tenant->fresh();
    expect($t->legal['vat_number'])->toBe('300123456700003')
        ->and($t->legal['address'])->toBe('West Bay, Doha')
        ->and($t->setting('payments.tax_rate'))->toEqual(5)
        ->and($t->setting('payments.tax_enabled'))->toBeTrue()
        ->and($t->setting('payments.client_can_pause'))->toBeTrue()
        ->and($t->setting('ai.keep'))->toBeTrue();

    Livewire::test(Business::class)->set('taxRate', '')->set('taxEnabled', false)->call('save');
    expect($tenant->fresh()->setting('payments.tax_rate'))->toBeNull()
        ->and($tenant->fresh()->setting('payments.tax_enabled'))->toBeFalse();
});

it('switches locale for the session and the user', function () {
    [, $owner] = $this->createCoachTenant();
    $this->actingAs($owner)->get('/locale/fr')->assertRedirect();
    expect($owner->fresh()->locale)->toBe('fr');
});

it('renders the branded public checkout page', function () {
    [$tenant] = $this->createCoachTenant([], ['name' => 'Atlas Performance']);
    app(TenantContext::class)->run($tenant, function () {
        $product = Product::query()->create(['type' => 'coaching_package', 'slug' => '12-week-transformation', 'name' => ['en' => '12-week transformation', 'ar' => 'برنامج التحول ١٢ أسبوعًا'], 'is_active' => true]);
        Price::query()->create(['product_id' => $product->id, 'type' => 'recurring', 'amount_minor' => 59000, 'currency' => 'QAR', 'interval' => 'month']);
    });
    $slug = $tenant->slug;
    $this->get("/checkout/{$slug}/12-week-transformation")->assertOk()
        ->assertSee('12-week transformation')->assertSee('Atlas Performance')
        ->assertSee('public\\\\\\/checkout', false)
        ->assertSee('Monthly', false)
        ->assertHeader('X-Robots-Tag', 'noindex');
    $this->get("/checkout/{$slug}/12-week-transformation?lang=ar")->assertOk()->assertSee('dir="rtl"', false)->assertSee('برنامج التحول ١٢ أسبوعًا');
    $this->get("/checkout/{$slug}/missing")->assertNotFound();
});
