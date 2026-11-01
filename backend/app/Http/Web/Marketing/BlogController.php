<?php

namespace App\Http\Web\Marketing;

use App\Domain\Marketing\Models\BlogPost;
use App\View\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Localized blog / academy. Posts are per-locale rows linked by translation_group. */
class BlogController
{
    public static function query(?string $locale = null): Builder
    {
        return BlogPost::query()->whereNotNull('published_at')->where('published_at', '<=', now())
            ->when($locale, fn ($q, $l) => $q->where('locale', $l));
    }

    public function index(string $locale): View
    {
        $posts = self::query($locale)->orderByDesc('published_at')->paginate(12);
        $seo = Seo::make(__('marketing.blog.meta_title'), __('marketing.blog.meta_description'))
            ->breadcrumbs([[__('marketing.nav.res.blog'), route('marketing.blog.index')]]);

        return view('marketing.blog.index', compact('seo', 'posts'));
    }

    public function show(string $locale, string $slug): View
    {
        $post = self::query($locale)->where('slug', $slug)->firstOrFail();

        $alternates = [$locale => route('marketing.blog.show', ['locale' => $locale, 'slug' => $post->slug])];
        if ($post->translation_group) {
            self::query()->where('translation_group', $post->translation_group)->get(['locale', 'slug'])
                ->each(function (BlogPost $p) use (&$alternates) {
                    $alternates[$p->locale] = route('marketing.blog.show', ['locale' => $p->locale, 'slug' => $p->slug]);
                });
        }

        $html = Str::markdown($post->body, ['html_input' => 'allow', 'allow_unsafe_links' => false]);
        $words = count(preg_split('/\s+/u', trim(strip_tags($html))) ?: []);
        $seo = Seo::make($post->meta_title ?: $post->title, $post->meta_description ?: $post->excerpt)
            ->alternates($alternates)
            ->breadcrumbs([[__('marketing.nav.res.blog'), route('marketing.blog.index')], [$post->title, url()->current()]])
            ->image($post->cover_url)
            ->type('article')
            ->noindex($post->noindex)
            ->schema(array_filter([
                '@type' => 'Article',
                'headline' => Str::limit($post->title, 110, ''),
                'description' => $post->excerpt,
                'image' => $post->cover_url,
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => $post->updated_at?->toIso8601String(),
                'inLanguage' => $post->locale,
                'wordCount' => $words,
                'author' => ['@type' => 'Organization', 'name' => config('brand.name')],
                'publisher' => ['@id' => rtrim(url('/'), '/').'/#organization'],
                'mainEntityOfPage' => url()->current(),
            ]));

        $related = self::query($locale)->where('id', '!=', $post->id)->when($post->category, fn ($q, $c) => $q->where('category', $c))->orderByDesc('published_at')->limit(3)->get();
        $minutes = max(1, (int) ceil($words / 220));

        return view('marketing.blog.show', compact('seo', 'post', 'html', 'related', 'minutes'));
    }
}
