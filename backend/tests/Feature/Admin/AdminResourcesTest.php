<?php

use App\Domain\Ai\Models\AiRequest;
use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Identity\Models\User;
use App\Domain\Marketing\Models\BlogPost;
use App\Domain\Marketing\Models\MarketingLead;
use App\Domain\Marketing\Models\Review;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Payments\Models\WebhookEvent;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\FeatureFlag;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Models\Translation;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Filament\Resources\Exercises\Pages\ListExercises;
use App\Filament\Resources\Foods\Pages\ListFoods;
use App\Filament\Resources\MarketingLeads\Tables\MarketingLeadsTable;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\Tenants\Pages\ViewTenant;
use App\Filament\Resources\Translations\Pages\ListTranslations;
use App\Filament\Resources\WebhookEvents\Pages\ListWebhookEvents;
use App\Filament\Support\TranslationSync;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_super_admin' => true])->save();

    [$this->tenantA, $this->ownerA] = $this->createCoachTenant();
    [$this->tenantB, $this->ownerB] = $this->createCoachTenant();
});

it('lists tenants of every business', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListTenants::class)
        ->assertCanSeeTableRecords([$this->tenantA, $this->tenantB]);
});

it('lists exercises across tenants and the global library, even inside a tenant context', function () {
    $global = Exercise::query()->create(['tenant_id' => null, 'slug' => 'back-squat', 'name' => ['en' => 'Back squat', 'ar' => 'سكوات خلفي']]);
    $exA = Exercise::query()->create(['tenant_id' => $this->tenantA->id, 'slug' => 'a-private', 'name' => ['en' => 'Tenant A move']]);
    $exB = Exercise::query()->create(['tenant_id' => $this->tenantB->id, 'slug' => 'b-private', 'name' => ['en' => 'Tenant B move']]);

    // A leaked tenant context must not narrow the admin's view.
    app(TenantContext::class)->set($this->tenantA);
    $this->actingAs($this->admin);

    Livewire::test(ListExercises::class)
        ->assertCanSeeTableRecords([$global, $exA, $exB])
        ->filterTable('global', true)
        ->assertCanSeeTableRecords([$global])
        ->assertCanNotSeeTableRecords([$exA, $exB]);

    app(TenantContext::class)->set(null);
    $this->get('/admin/exercises/'.$exB->id.'/edit')->assertOk()->assertSee('b-private');
});

it('renders view and edit pages for records of every resource', function () {
    $this->actingAs($this->admin);
    $client = $this->addClient($this->tenantA, $this->ownerA);

    $food = Food::query()->create(['tenant_id' => $this->tenantB->id, 'name' => ['en' => 'Karak tea'], 'kcal' => 60, 'moderation' => 'pending', 'source' => 'user']);
    $recipe = Recipe::query()->create(['tenant_id' => null, 'slug' => 'machboos', 'name' => ['en' => 'Chicken machboos'], 'steps' => ['en' => ['Rinse rice', 'Cook']]]);
    $program = Program::query()->create(['tenant_id' => null, 'name' => ['en' => 'Hypertrophy 8 weeks'], 'visibility' => 'public', 'is_template' => true]);
    $post = BlogPost::query()->create(['locale' => 'en', 'slug' => 'how-to-train', 'title' => 'How to train', 'body' => '# Train']);
    $profile = CoachProfile::query()->create(['tenant_id' => $this->tenantA->id, 'user_id' => $this->ownerA->id, 'slug' => 'coach-a']);
    Review::query()->create(['tenant_id' => $this->tenantA->id, 'coach_profile_id' => $profile->id, 'client_id' => $client->id, 'rating' => 5, 'body' => 'Excellent']);
    $event = WebhookEvent::query()->create(['driver' => 'stripe_saas', 'external_id' => 'evt_1', 'type' => 'invoice.paid', 'payload' => ['id' => 'evt_1', 'type' => 'invoice.paid']]);
    $flag = FeatureFlag::query()->create(['key' => 'beta_x', 'enabled' => true]);
    AiRequest::query()->create(['tenant_id' => $this->tenantA->id, 'feature' => 'program_builder', 'provider' => 'fake', 'model' => 'fake-1', 'tokens_in' => 100, 'tokens_out' => 50, 'cost_micros' => 1200, 'status' => 'ok']);
    MarketingLead::query()->create(['email' => 'lead@example.com', 'kind' => 'waitlist', 'consent' => true]);
    Translation::query()->create(['locale' => 'fr', 'group' => 'billing', 'key' => 'title', 'value' => 'Facturation']);
    $subscription = $this->tenantA->subscriptions()->first();

    $urls = [
        '/admin/tenants/'.$this->tenantA->id,
        '/admin/tenants/'.$this->tenantA->id.'/edit',
        '/admin/users/'.$this->ownerB->id,
        '/admin/users/'.$this->ownerB->id.'/edit',
        '/admin/foods/'.$food->id.'/edit',
        '/admin/recipes/'.$recipe->id.'/edit',
        '/admin/programs/'.$program->id.'/edit',
        '/admin/blog-posts/'.$post->id.'/edit',
        '/admin/coach-profiles/'.$profile->id.'/edit',
        '/admin/webhook-events/'.$event->id,
        '/admin/feature-flags/'.$flag->id.'/edit',
        '/admin/saas-plans/'.SaasPlan::query()->where('code', 'pro')->value('id').'/edit',
        '/admin/foods', '/admin/reviews', '/admin/ai-requests', '/admin/marketing-leads', '/admin/translations',
        '/admin/activity-logs', '/admin/programs', '/admin/recipes', '/admin/blog-posts', '/admin/coach-profiles',
        '/admin', '/admin/system-health', '/admin/white-label-apps',
    ];
    if ($subscription) {
        $urls[] = '/admin/saas-subscriptions/'.$subscription->id;
    }

    foreach ($urls as $url) {
        $response = $this->get($url);
        expect($response->status())->toBe(200, "GET {$url} returned {$response->status()}");
    }

    $this->get('/admin/webhook-events/'.$event->id)->assertSee('invoice.paid');
});

it('moderates user-submitted foods', function () {
    $this->actingAs($this->admin);
    $food = Food::query()->create(['tenant_id' => $this->tenantA->id, 'name' => ['en' => 'Shawarma'], 'kcal' => 250, 'moderation' => 'pending', 'source' => 'user']);
    $other = Food::query()->create(['tenant_id' => $this->tenantB->id, 'name' => ['en' => 'Junk'], 'kcal' => 900, 'moderation' => 'pending', 'source' => 'user']);

    Livewire::test(ListFoods::class)
        ->assertCanSeeTableRecords([$food, $other])
        ->callTableAction('approve', $food, ['verify' => true])
        ->callTableAction('reject', $other);

    expect($food->refresh()->moderation)->toBe('approved')
        ->and($food->is_verified)->toBeTrue()
        ->and(Food::withoutGlobalScopes()->find($other->id)->moderation)->toBe('rejected')
        ->and(ActivityLog::query()->where('event', 'admin.food.approved')->exists())->toBeTrue();
});

it('approves reviews and refreshes the coach rating', function () {
    $this->actingAs($this->admin);
    $client = $this->addClient($this->tenantA, $this->ownerA);
    $profile = CoachProfile::query()->create(['tenant_id' => $this->tenantA->id, 'user_id' => $this->ownerA->id, 'slug' => 'coach-rating']);
    $review = Review::query()->create(['tenant_id' => $this->tenantA->id, 'coach_profile_id' => $profile->id, 'client_id' => $client->id, 'rating' => 4]);

    Livewire::test(ListReviews::class)->callTableAction('approve', $review);

    expect($review->refresh()->status)->toBe('approved')
        ->and((float) $profile->refresh()->rating_avg)->toBe(4.0)
        ->and($profile->rating_count)->toBe(1);
});

it('suspends a tenant and changes its plan with an audit trail', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewTenant::class, ['record' => $this->tenantB->id])
        ->callAction('suspend', ['reason' => 'Chargeback fraud'])
        ->callAction('changePlan', ['plan' => 'studio', 'bill' => false]);

    $tenant = $this->tenantB->refresh();
    expect($tenant->setting('suspended_reason'))->toBe('Chargeback fraud')
        ->and($tenant->plan)->toBe('studio')
        ->and(ActivityLog::query()->where('event', 'admin.tenant.suspended')->where('tenant_id', $tenant->id)->exists())->toBeTrue()
        ->and(ActivityLog::query()->where('event', 'admin.tenant.plan_changed')->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

it('replays a failed stripe_saas webhook through the billing service', function () {
    $this->actingAs($this->admin);
    $event = WebhookEvent::query()->create([
        'driver' => 'stripe_saas', 'external_id' => 'evt_replay', 'type' => 'customer.subscription.updated',
        'payload' => ['id' => 'evt_replay', 'type' => 'customer.subscription.updated', 'data' => ['object' => ['id' => 'sub_missing']]],
        'status' => 'failed', 'error' => 'Temporary outage',
    ]);
    $other = WebhookEvent::query()->create(['driver' => 'stripe', 'external_id' => 'evt_coach', 'type' => 'payment_intent.succeeded', 'payload' => [], 'status' => 'failed']);

    Livewire::test(ListWebhookEvents::class)
        ->callTableAction('replay', $event)
        ->callTableAction('replay', $other);

    expect($event->refresh()->status)->toBe('processed')
        ->and($event->error)->toBeNull()
        ->and($other->refresh()->status)->toBe('received')
        ->and(ActivityLog::query()->where('event', 'admin.webhook.replayed')->exists())->toBeTrue();
});

it('syncs translations from lang files without overwriting admin edits', function () {
    $this->actingAs($this->admin);
    $dir = sys_get_temp_dir().'/aurum-lang-'.uniqid();
    mkdir($dir.'/en', 0777, true);
    file_put_contents($dir.'/en/demo.php', "<?php return ['title' => 'Hello', 'nested' => ['k' => 'Deep']];");
    file_put_contents($dir.'/fr.json', json_encode(['Platform' => 'Plateforme']));
    Translation::query()->create(['locale' => 'en', 'group' => 'demo', 'key' => 'title', 'value' => 'Edited by admin']);

    $result = app(TranslationSync::class)->sync(false, $dir);

    expect($result)->toBe(['created' => 2, 'updated' => 0])
        ->and(Translation::query()->where(['locale' => 'en', 'group' => 'demo', 'key' => 'title'])->value('value'))->toBe('Edited by admin')
        ->and(Translation::query()->where(['locale' => 'en', 'group' => 'demo', 'key' => 'nested.k'])->value('value'))->toBe('Deep')
        ->and(Translation::query()->where(['locale' => 'fr', 'group' => '*', 'key' => 'Platform'])->value('value'))->toBe('Plateforme');

    expect(app(TranslationSync::class)->sync(true, $dir))->toBe(['created' => 0, 'updated' => 1]);

    Livewire::test(ListTranslations::class)->callTableAction('syncFromLangFiles', data: ['overwrite' => false])->assertHasNoTableActionErrors();
    expect(Translation::query()->where('group', 'billing')->exists())->toBeTrue();

    File::deleteDirectory($dir);
});

it('exports marketing leads as CSV', function () {
    $this->actingAs($this->admin);
    MarketingLead::query()->create(['email' => '=cmd@example.com', 'kind' => 'waitlist', 'consent' => true]);
    MarketingLead::query()->create(['email' => 'ok@example.com', 'kind' => 'demo', 'consent' => false]);

    $csv = MarketingLeadsTable::csv(MarketingLead::query())->getCallback();
    ob_start();
    $csv();
    $out = (string) ob_get_clean();

    expect($out)->toContain('email,kind,resource,locale,consent,created_at')
        ->toContain("'=cmd@example.com")
        ->toContain('ok@example.com,demo');
});
