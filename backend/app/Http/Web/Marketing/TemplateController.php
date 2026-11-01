<?php

namespace App\Http\Web\Marketing;

use App\Domain\Marketing\Models\MarketingLead;
use App\Domain\Workouts\Models\Program;
use App\View\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/** Free program templates (programs.visibility = public). Download is gated by email. */
class TemplateController
{
    public static function query(): Builder
    {
        return Program::query()->withoutGlobalScopes()->where('visibility', 'public')->whereNull('deleted_at')->whereNotNull('slug');
    }

    public function index(string $locale): View
    {
        $templates = self::query()->orderByDesc('updated_at')->paginate(18);
        $seo = Seo::make(__('marketing.templates.meta_title'), __('marketing.templates.meta_description'))
            ->breadcrumbs([[__('marketing.nav.res.templates'), route('marketing.templates.index')]]);

        return view('marketing.templates.index', compact('seo', 'templates'));
    }

    public function show(Request $request, string $locale, string $slug): View
    {
        $program = self::query()->where('slug', $slug)->withCount('weeks')->firstOrFail();
        $name = (string) $program->translate('name');
        $seo = Seo::make(__('marketing.templates.show_title', ['name' => $name]), (string) ($program->translate('description') ?: __('marketing.templates.meta_description')))
            ->breadcrumbs([[__('marketing.nav.res.templates'), route('marketing.templates.index')], [$name, url()->current()]])
            ->image($program->cover_url)
            ->schema([
                '@type' => 'Product',
                'name' => $name,
                'description' => (string) ($program->translate('description') ?: __('marketing.templates.meta_description')),
                'image' => $program->cover_url ?: route('marketing.og'),
                'brand' => ['@type' => 'Brand', 'name' => config('brand.name')],
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock', 'url' => url()->current()],
            ]);

        return view('marketing.templates.show', ['seo' => $seo, 'program' => $program, 'name' => $name, 'error' => $request->query('error')]);
    }

    public function lead(Request $request, string $locale, string $slug): RedirectResponse
    {
        $program = self::query()->where('slug', $slug)->firstOrFail();
        if (filled($request->input('website'))) {
            return redirect()->route('marketing.templates.show', ['locale' => $locale, 'slug' => $slug]);
        }
        $validator = validator($request->all(), ['email' => ['required', 'email:rfc', 'max:190'], 'consent' => ['nullable']]);
        if ($validator->fails()) {
            return redirect()->route('marketing.templates.show', ['locale' => $locale, 'slug' => $slug, 'error' => 1]).'#download';
        }

        MarketingLead::create([
            'email' => strtolower((string) $request->input('email')),
            'kind' => 'template_download',
            'resource' => $program->slug,
            'locale' => $locale,
            'consent' => $request->boolean('consent'),
        ]);

        return redirect()->to(URL::temporarySignedRoute('marketing.templates.download', now()->addDays(7), ['locale' => $locale, 'slug' => $slug]));
    }

    public function download(string $locale, string $slug): View
    {
        $program = self::query()->where('slug', $slug)
            ->with(['weeks.days' => fn ($q) => $q->orderBy('day_number'), 'weeks.days.workout' => fn ($q) => $q->withoutGlobalScopes(), 'weeks.days.workout.blocks' => fn ($q) => $q->orderBy('position'), 'weeks.days.workout.blocks.exercises' => fn ($q) => $q->orderBy('position'), 'weeks.days.workout.blocks.exercises.exercise' => fn ($q) => $q->withoutGlobalScopes(), 'weeks.days.workout.blocks.exercises.sets' => fn ($q) => $q->orderBy('position')])
            ->firstOrFail();

        return view('marketing.templates.download', ['program' => $program, 'name' => (string) $program->translate('name')]);
    }
}
