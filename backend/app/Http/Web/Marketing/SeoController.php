<?php

namespace App\Http\Web\Marketing;

use App\Domain\Marketing\Models\BlogPost;
use App\Http\Middleware\SetLocale;
use App\View\Marketing\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/** robots.txt, XML sitemaps (index + per locale/type), app-link association files, brand assets. */
class SeoController
{
    public const TYPES = ['pages', 'exercises', 'coaches', 'templates', 'blog'];

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /app',
            'Disallow: /admin',
            'Disallow: /livewire',
            'Disallow: /api/',
            'Disallow: /*/templates/*/download',
            'Disallow: /*?q=',
            '',
            'Sitemap: '.route('marketing.sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }

    public function index(): Response
    {
        $xml = Cache::remember('sitemap:index', 900, function () {
            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach (SetLocale::SUPPORTED as $locale) {
                foreach (self::TYPES as $type) {
                    $out .= '  <sitemap><loc>'.e(route('marketing.sitemap.show', ['locale' => $locale, 'type' => $type])).'</loc><lastmod>'.$this->lastmod($type, $locale)->toAtomString().'</lastmod></sitemap>'."\n";
                }
            }

            return $out.'</sitemapindex>'."\n";
        });

        return $this->xml($xml);
    }

    public function sitemap(string $locale, string $type): Response
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true) && in_array($type, self::TYPES, true), 404);
        $xml = Cache::remember("sitemap:{$locale}:{$type}", 900, function () use ($locale, $type) {
            $urls = match ($type) {
                'pages' => $this->pages($locale),
                'exercises' => $this->exercises(),
                'coaches' => $this->coaches(),
                'templates' => $this->templates(),
                'blog' => $this->blog($locale),
            };
            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";
            foreach ($urls as [$route, $params, $lastmod, $locales]) {
                $locales ??= SetLocale::SUPPORTED;
                $out .= '  <url><loc>'.e(route($route, $params + ['locale' => $locale])).'</loc>';
                $out .= '<lastmod>'.Carbon::parse($lastmod)->toAtomString().'</lastmod>';
                foreach ($locales as $alt => $altParams) {
                    [$altLocale, $altParams] = is_string($alt) ? [$alt, $altParams] : [$altParams, $params];
                    $out .= '<xhtml:link rel="alternate" hreflang="'.$altLocale.'" href="'.e(route($route, $altParams + ['locale' => $altLocale])).'"/>';
                }
                if (! is_string(array_key_first($locales))) {
                    $out .= '<xhtml:link rel="alternate" hreflang="x-default" href="'.e(route($route, $params + ['locale' => 'en'])).'"/>';
                }
                $out .= '</url>'."\n";
            }

            return $out.'</urlset>'."\n";
        });

        return $this->xml($xml);
    }

    public function appleAppSiteAssociation(): JsonResponse
    {
        $team = config('brand.apple_team_id');
        $bundles = (array) config('services.apple.bundle_ids', []);
        $appIds = $team ? array_values(array_map(fn ($b) => $team.'.'.$b, $bundles)) : [];

        return response()->json([
            'applinks' => [
                'details' => $appIds === [] ? [] : [[
                    'appIDs' => $appIds,
                    'components' => [
                        ['/' => '/invite/*', 'comment' => 'Client & staff invitations'],
                        ['/' => '/auth/magic/*', 'comment' => 'Magic sign-in links'],
                        ['/' => '/*/coaches/*', 'comment' => 'Coach profiles'],
                        ['/' => '/*/exercises/*', 'comment' => 'Exercise library'],
                        ['/' => '/*/templates/*', 'comment' => 'Program templates'],
                    ],
                ]],
            ],
            'webcredentials' => ['apps' => $appIds],
        ], 200, ['Cache-Control' => 'public, max-age=3600'], JSON_UNESCAPED_SLASHES);
    }

    public function assetLinks(): JsonResponse
    {
        $fingerprints = array_values((array) config('brand.android_sha256', []));
        $statements = [];
        foreach (array_filter([config('brand.android_package'), config('brand.android_coach_package')]) as $package) {
            $statements[] = [
                'relation' => ['delegate_permission/common.handle_all_urls', 'delegate_permission/common.get_login_creds'],
                'target' => ['namespace' => 'android_app', 'package_name' => $package, 'sha256_cert_fingerprints' => $fingerprints],
            ];
        }

        return response()->json($statements, 200, ['Cache-Control' => 'public, max-age=3600'], JSON_UNESCAPED_SLASHES);
    }

    public function logo(): Response
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="512" height="512"><defs><linearGradient id="g" x1="4" y1="2" x2="28" y2="30" gradientUnits="userSpaceOnUse"><stop stop-color="#F1DDB1"/><stop offset=".5" stop-color="#D4AF6A"/><stop offset="1" stop-color="#9C7A36"/></linearGradient></defs><rect x="1" y="1" width="30" height="30" rx="9" fill="#0B0B0F" stroke="url(#g)" stroke-opacity=".55"/><path d="M9 23 16 8l7 15" fill="none" stroke="url(#g)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.6 17.6h8.8" stroke="url(#g)" stroke-width="2.4" stroke-linecap="round"/></svg>';

        return response($svg, 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=604800, immutable']);
    }

    /** Default 1200×630 Open Graph image (PNG — social networks do not accept SVG). */
    public function ogImage(): Response
    {
        $png = Cache::remember('og:default:'.config('brand.name'), 86400, function () {
            $w = 1200;
            $h = 630;
            $img = imagecreatetruecolor($w, $h);
            imageantialias($img, true);
            for ($y = 0; $y < $h; $y++) {
                $t = $y / $h;
                $c = imagecolorallocate($img, (int) (11 + 6 * $t), (int) (11 + 5 * $t), (int) (15 + 8 * $t));
                imageline($img, 0, $y, $w, $y, $c);
            }
            // Soft gold glow, top-end corner.
            $glow = imagecolorallocatealpha($img, 212, 175, 106, 124);
            for ($r = 520; $r > 0; $r -= 20) {
                imagefilledellipse($img, 1080, 40, $r * 2, $r * 2, $glow);
            }
            $gold = imagecolorallocate($img, 212, 175, 106);
            $goldSoft = imagecolorallocatealpha($img, 212, 175, 106, 90);
            imagesetthickness($img, 1);
            imagerectangle($img, 40, 40, $w - 41, $h - 41, $goldSoft);
            // Brand mark: rounded tile + "A".
            imagesetthickness($img, 14);
            imageline($img, 120, 420, 210, 220, $gold);
            imageline($img, 210, 220, 300, 420, $gold);
            imageline($img, 152, 350, 268, 350, $gold);
            imagesetthickness($img, 3);
            imageline($img, 360, 220, 360, 420, $goldSoft);
            // Wordmark (scaled bitmap font for portability: no system fonts required).
            $text = strtoupper((string) config('brand.name'));
            $tw = imagefontwidth(5) * strlen($text);
            $th = imagefontheight(5);
            $layer = imagecreatetruecolor($tw, $th);
            imagesavealpha($layer, true);
            $transparent = imagecolorallocatealpha($layer, 0, 0, 0, 127);
            imagefill($layer, 0, 0, $transparent);
            imagestring($layer, 5, 0, 0, $text, imagecolorallocate($layer, 245, 243, 238));
            $scale = 9;
            imagecopyresampled($img, $layer, 410, 250, 0, 0, $tw * $scale, $th * $scale, $tw, $th);
            imagedestroy($layer);
            ob_start();
            imagepng($img, null, 6);
            imagedestroy($img);

            return (string) ob_get_clean();
        });

        return response($png, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=604800']);
    }

    private function xml(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=900']);
    }

    /** @return list<array{0: string, 1: array, 2: mixed, 3: ?array}> */
    private function pages(string $locale): array
    {
        $deploy = Carbon::createFromTimestamp((int) (@filemtime(base_path('routes/web/marketing.php')) ?: time()));

        return array_map(fn ($p) => [$p[0], $p[1], $deploy, null], Catalog::staticPages());
    }

    private function exercises(): array
    {
        return ExerciseController::query()->orderBy('slug')->limit(45000)->get(['slug', 'updated_at'])
            ->map(fn ($e) => ['marketing.exercises.show', ['slug' => $e->slug], $e->updated_at ?? now(), null])->all();
    }

    private function coaches(): array
    {
        $rows = CoachController::query()->orderBy('slug')->limit(45000)->get(['slug', 'updated_at'])
            ->map(fn ($c) => ['marketing.coaches.show', ['slug' => $c->slug], $c->updated_at ?? now(), null])->all();
        foreach (CoachController::citySlugs() as $city) {
            $rows[] = ['marketing.coaches.city', ['city' => $city], now()->startOfDay(), null];
        }

        return $rows;
    }

    private function templates(): array
    {
        return TemplateController::query()->orderBy('slug')->get(['slug', 'updated_at'])
            ->map(fn ($p) => ['marketing.templates.show', ['slug' => $p->slug], $p->updated_at ?? now(), null])->all();
    }

    private function blog(string $locale): array
    {
        $posts = BlogController::query($locale)->where('noindex', false)->orderByDesc('published_at')->get(['slug', 'translation_group', 'updated_at', 'published_at']);
        $groups = $posts->pluck('translation_group')->filter()->unique()->values();
        $siblings = $groups->isEmpty() ? collect() : BlogController::query()->whereIn('translation_group', $groups)->get(['locale', 'slug', 'translation_group'])->groupBy('translation_group');

        return $posts->map(function (BlogPost $p) use ($siblings, $locale) {
            $alts = [$locale => ['slug' => $p->slug]];
            foreach ($siblings[$p->translation_group] ?? [] as $s) {
                $alts[$s->locale] = ['slug' => $s->slug];
            }

            return ['marketing.blog.show', ['slug' => $p->slug], $p->updated_at ?? $p->published_at, $alts];
        })->all();
    }

    private function lastmod(string $type, string $locale): Carbon
    {
        $value = match ($type) {
            'exercises' => ExerciseController::query()->max('updated_at'),
            'coaches' => CoachController::query()->max('updated_at'),
            'templates' => TemplateController::query()->max('updated_at'),
            'blog' => BlogController::query($locale)->max('updated_at'),
            default => null,
        };

        return $value ? Carbon::parse($value) : Carbon::createFromTimestamp((int) (@filemtime(base_path('routes/web/marketing.php')) ?: time()));
    }
}
