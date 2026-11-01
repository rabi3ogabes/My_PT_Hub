<?php

use App\Domain\Workouts\Models\Exercise;

it('serves a valid sitemap index and per-locale sitemaps', function () {
    Exercise::query()->create(['tenant_id' => null, 'slug' => 'push-up', 'name' => ['en' => 'Push-up'], 'level' => 'beginner', 'is_published' => true]);

    $index = $this->get('/sitemap.xml');
    $index->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $xml = simplexml_load_string($index->getContent());
    expect($xml)->not->toBeFalse()->and($xml->getName())->toBe('sitemapindex')->and(count($xml->sitemap))->toBe(20);

    foreach (['en', 'ar', 'fr', 'es'] as $locale) {
        foreach (['pages', 'exercises', 'coaches', 'templates', 'blog'] as $type) {
            $res = $this->get("/sitemaps/{$locale}/{$type}.xml");
            $res->assertOk();
            $doc = simplexml_load_string($res->getContent());
            expect($doc)->not->toBeFalse()->and($doc->getName())->toBe('urlset');
        }
    }
    $pages = $this->get('/sitemaps/ar/pages.xml')->getContent();
    expect($pages)->toContain('/ar/pricing')->toContain('hreflang="x-default"')->toContain('<lastmod>');
    expect($this->get('/sitemaps/fr/exercises.xml')->getContent())->toContain('/fr/exercises/push-up');
});

it('serves robots.txt pointing at the sitemap', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'), false)->assertSee('Disallow: /app', false);
});

it('serves app association files from brand config', function () {
    config(['brand.apple_team_id' => 'TEAM123456', 'services.apple.bundle_ids' => ['coach.aurum.client'], 'brand.android_sha256' => ['AA:BB']]);
    $this->get('/.well-known/apple-app-site-association')->assertOk()
        ->assertJsonPath('applinks.details.0.appIDs.0', 'TEAM123456.coach.aurum.client')
        ->assertJsonPath('webcredentials.apps.0', 'TEAM123456.coach.aurum.client');
    $this->get('/.well-known/assetlinks.json')->assertOk()
        ->assertJsonPath('0.target.package_name', config('brand.android_package'))
        ->assertJsonPath('0.target.sha256_cert_fingerprints.0', 'AA:BB');
});

it('serves the default Open Graph image and logo', function () {
    $this->get('/brand/og.png')->assertOk()->assertHeader('Content-Type', 'image/png');
    $this->get('/brand/logo.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
});

it('adds the smart app banner when an iOS app id is configured', function () {
    $this->withoutVite();
    config(['brand.ios_app_id' => '1234567890']);
    $this->get('/en')->assertSee('name="apple-itunes-app" content="app-id=1234567890', false);
});
