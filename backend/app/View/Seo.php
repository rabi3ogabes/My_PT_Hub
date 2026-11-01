<?php

namespace App\View;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

/**
 * Per-page SEO metadata for the marketing layout: title, description, canonical,
 * hreflang alternates, Open Graph image and a JSON-LD @graph.
 */
final class Seo
{
    public ?string $description = null;

    public ?string $canonical = null;

    /** @var array<string, string> locale => absolute URL */
    public array $alternates = [];

    public ?string $image = null;

    public string $type = 'website';

    public bool $noindex = false;

    /** @var list<array<string, mixed>> */
    public array $schema = [];

    /** @var list<array{0: string, 1: string}> */
    public array $breadcrumbs = [];

    public function __construct(public string $title) {}

    public static function make(string $title, ?string $description = null): self
    {
        $seo = new self($title);
        $seo->description = $description;
        $seo->alternates = self::currentAlternates();
        $seo->canonical = $seo->alternates[app()->getLocale()] ?? url()->current();

        return $seo;
    }

    /** hreflang URLs for the current route in every locale (same route, locale swapped). */
    public static function currentAlternates(): array
    {
        $route = Route::current();
        if (! $route || ! $route->getName() || ! array_key_exists('locale', $route->parameters())) {
            return [];
        }
        $out = [];
        foreach (SetLocale::SUPPORTED as $locale) {
            $out[$locale] = route($route->getName(), array_merge($route->parameters(), ['locale' => $locale]));
        }

        return $out;
    }

    /** @param  array<string, string>  $alternates */
    public function alternates(array $alternates): self
    {
        $this->alternates = $alternates;
        $this->canonical = $alternates[app()->getLocale()] ?? $this->canonical;

        return $this;
    }

    public function image(?string $url): self
    {
        $this->image = $url;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function noindex(bool $noindex = true): self
    {
        $this->noindex = $noindex;

        return $this;
    }

    /** @param  array<string, mixed>  $node */
    public function schema(array $node): self
    {
        $this->schema[] = $node;

        return $this;
    }

    /** @param  list<array{0: string, 1: string}>  $crumbs  [label, url] pairs (home is prepended) */
    public function breadcrumbs(array $crumbs): self
    {
        $this->breadcrumbs = array_merge([[__('marketing.nav.home'), route('marketing.home')]], $crumbs);

        return $this;
    }

    /** @param  list<array{0: string, 1: string}>  $faq  [question, answer] pairs */
    public function faq(array $faq): self
    {
        if ($faq === []) {
            return $this;
        }

        return $this->schema([
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => [
                '@type' => 'Question',
                'name' => $qa[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
            ], $faq),
        ]);
    }

    public function imageUrl(): string
    {
        return $this->image ?? route('marketing.og', ['locale' => app()->getLocale()]);
    }

    public function fullTitle(): string
    {
        $brand = config('brand.name');

        return str_contains($this->title, $brand) ? $this->title : $this->title.' · '.$brand;
    }

    /** Organization + WebSite graph shared by every page, plus page nodes. */
    public function jsonLd(): string
    {
        $base = rtrim(url('/'), '/');
        $locale = app()->getLocale();
        $graph = [
            [
                '@type' => 'Organization',
                '@id' => $base.'/#organization',
                'name' => config('brand.name'),
                'url' => $base,
                'logo' => route('marketing.logo'),
                'email' => config('brand.support_email'),
            ],
            [
                '@type' => 'WebSite',
                '@id' => $base.'/#website',
                'url' => $base,
                'name' => config('brand.name'),
                'inLanguage' => $locale,
                'publisher' => ['@id' => $base.'/#organization'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('marketing.exercises.index', ['locale' => $locale]).'?q={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ];
        if (count($this->breadcrumbs) > 1) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => array_map(fn ($c, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $c[0],
                    'item' => $c[1],
                ], $this->breadcrumbs, array_keys($this->breadcrumbs)),
            ];
        }
        foreach ($this->schema as $node) {
            $graph[] = $node;
        }

        return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}
