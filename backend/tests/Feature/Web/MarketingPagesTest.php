<?php

use App\Domain\Identity\Models\User;
use App\Domain\Marketing\Models\BlogPost;
use App\Domain\Marketing\Models\Review;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Services\MembershipService;
use App\Domain\Tenancy\Services\TenantProvisioner;
use App\View\Marketing\Catalog;

beforeEach(function () {
    $this->withoutVite();
});

/** Every static marketing path (without the locale prefix). */
function marketingPaths(): array
{
    $paths = ['', 'features', 'pricing', 'switch', 'about', 'contact', 'calculators', 'exercises', 'templates', 'coaches', 'coaches/city/doha', 'blog'];
    foreach (array_keys(Catalog::FEATURES) as $s) {
        $paths[] = 'features/'.$s;
    }
    foreach (array_keys(Catalog::SOLUTIONS) as $s) {
        $paths[] = 'solutions/'.$s;
    }
    foreach (array_keys(Catalog::COMPETITORS) as $s) {
        $paths[] = 'compare/'.$s;
    }
    foreach (array_keys(Catalog::CALCULATORS) as $s) {
        $paths[] = 'calculators/'.$s;
    }
    foreach (Catalog::LEGAL as $s) {
        $paths[] = 'legal/'.$s;
    }

    return $paths;
}

function seedPublicContent(): array
{
    $owner = User::factory()->create(['name' => 'Sara Haddad']);
    $tenant = app(TenantProvisioner::class)->provision($owner, ['name' => 'Haddad Coaching']);
    app(TenantContext::class)->set(null);
    $exercise = Exercise::query()->create([
        'tenant_id' => null,
        'slug' => 'barbell-back-squat',
        'name' => ['en' => 'Barbell back squat', 'ar' => 'القرفصاء الخلفي بالبار', 'fr' => 'Squat arrière à la barre', 'es' => 'Sentadilla trasera con barra'],
        'instructions' => ['en' => ['Set the bar on your upper back.', 'Sit down between your hips.', 'Drive up through mid-foot.'], 'ar' => ['ضع البار على أعلى ظهرك.', 'انزل بين وركيك.', 'ادفع للأعلى من منتصف القدم.']],
        'mistakes' => ['en' => ['Knees caving in'], 'ar' => ['انهيار الركبتين للداخل']],
        'primary_muscle' => 'quads',
        'level' => 'intermediate',
        'mechanics' => 'compound',
        'equipment' => ['barbell'],
        'is_published' => true,
    ]);
    $program = Program::query()->create([
        'tenant_id' => null,
        'slug' => 'strength-foundations',
        'name' => ['en' => 'Strength foundations', 'ar' => 'أساسيات القوة'],
        'description' => ['en' => 'A 4-week full-body plan.'],
        'visibility' => 'public',
        'level' => 'beginner',
        'days_per_week' => 3,
    ]);
    $profile = CoachProfile::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    $profile->forceFill(['is_published' => true, 'city' => 'Doha', 'headline' => 'Strength coach', 'offers_in_person' => true])->save();
    $client = User::factory()->create(['name' => 'Omar Khalil']);
    app(MembershipService::class)->add($tenant, $client, 'client', $owner->id, enforceLimits: false);
    app(TenantContext::class)->run($tenant, fn () => Review::query()->create(['coach_profile_id' => $profile->id, 'client_id' => $client->id, 'rating' => 5, 'body' => 'Changed how I train.', 'status' => 'approved']));
    foreach (['en', 'ar', 'fr', 'es'] as $l) {
        BlogPost::query()->create(['locale' => $l, 'slug' => 'first-post-'.$l, 'title' => 'Post '.$l, 'excerpt' => 'Excerpt', 'body' => "## Heading\n\nBody text.", 'translation_group' => '01JTRANSLATIONGROUP0000000', 'published_at' => now()->subDay()]);
    }

    return compact('exercise', 'program', 'profile');
}

it('renders every marketing page in every locale with lang, dir and hreflang', function (string $locale) {
    foreach (marketingPaths() as $path) {
        $url = '/'.$locale.($path === '' ? '' : '/'.$path);
        $response = $this->get($url);
        expect($response->status())->toBe(200, "{$url} returned {$response->status()}");
        $html = $response->getContent();
        $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        expect($html)->toContain('<html lang="'.$locale.'" dir="'.$dir.'"')
            ->and($html)->toContain('hreflang="x-default"')
            ->and(substr_count($html, 'rel="alternate" hreflang='))->toBeGreaterThanOrEqual(5)
            ->and($html)->toContain('application/ld+json')
            ->and($html)->toContain('rel="canonical"');
        expect($response->headers->get('Cache-Control'))->toContain('public');
        expect($response->headers->get('Set-Cookie'))->toBeNull();
    }
})->with(['en', 'ar', 'fr', 'es']);

it('renders database-driven detail pages in all locales', function () {
    $c = seedPublicContent();
    foreach (['en', 'ar', 'fr', 'es'] as $locale) {
        foreach (["exercises/{$c['exercise']->slug}", "templates/{$c['program']->slug}", "coaches/{$c['profile']->slug}", 'coaches/city/doha', "blog/first-post-{$locale}"] as $path) {
            $response = $this->get("/{$locale}/{$path}");
            expect($response->status())->toBe(200, "/{$locale}/{$path}");
            expect($response->getContent())->toContain('hreflang=');
        }
    }
    $this->get('/ar/exercises/barbell-back-squat')->assertSee('القرفصاء الخلفي بالبار')->assertSee('"HowTo"', false);
    $this->get('/en/coaches/'.$c['profile']->slug)->assertSee('"AggregateRating"', false)->assertSee('Changed how I train.');
    $this->get('/en/pricing')->assertSee('"FAQPage"', false)->assertSee('"SoftwareApplication"', false)->assertDontSee('aggregateRating', false);
    // Blog alternates only point to existing translations.
    $this->get('/fr/blog/first-post-fr')->assertSee('/es/blog/first-post-es', false);
    $this->get('/en/exercises?q=squat')->assertOk()->assertSee('Barbell back squat');
});

it('returns 404 for unknown slugs', function () {
    $this->get('/en/features/teleportation')->assertNotFound();
    $this->get('/en/compare/unknown')->assertNotFound();
    $this->get('/en/exercises/missing')->assertNotFound();
    $this->get('/de/pricing')->assertNotFound();
});

it('redirects the root to the best locale from Accept-Language', function () {
    $this->get('/', ['Accept-Language' => 'ar-QA,ar;q=0.9'])->assertRedirect('/ar');
    $this->get('/', ['Accept-Language' => 'fr-FR'])->assertRedirect('/fr');
    $this->get('/', ['Accept-Language' => 'de-DE'])->assertRedirect('/en');
});

it('stores contact requests as marketing leads', function () {
    $this->post('/en/contact', ['name' => 'Sara', 'email' => 'sara@example.com', 'topic' => 'demo', 'message' => 'I coach 30 clients in Doha.', 'consent' => '1'])
        ->assertRedirect('/en/contact?sent=1');
    $this->assertDatabaseHas('marketing_leads', ['email' => 'sara@example.com', 'kind' => 'demo', 'locale' => 'en']);
    $this->get('/en/contact?sent=1')->assertSee(__('marketing.contact.sent_title'));
});

it('gates template downloads behind an email and a signed url', function () {
    $c = seedPublicContent();
    $response = $this->post('/en/templates/'.$c['program']->slug, ['email' => 'coach@example.com']);
    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('signature=');
    $this->assertDatabaseHas('marketing_leads', ['email' => 'coach@example.com', 'kind' => 'template_download', 'resource' => 'strength-foundations']);
    $this->get($response->headers->get('Location'))->assertOk()->assertSee('Strength foundations');
    $this->get('/en/templates/'.$c['program']->slug.'/download')->assertForbidden();
});
