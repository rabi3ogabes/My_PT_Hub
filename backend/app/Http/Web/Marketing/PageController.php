<?php

namespace App\Http\Web\Marketing;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Marketing\Models\MarketingLead;
use App\Domain\Marketing\Models\Review;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Workouts\Models\Exercise;
use App\View\Marketing\Catalog;
use App\View\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Static marketing pages (home, features, solutions, pricing, compare, legal, calculators...). */
class PageController
{
    public function home(string $locale): View
    {
        $seo = Seo::make(__('marketing.home.meta_title'), __('marketing.home.meta_description'))
            ->schema($this->softwareApplication());

        return view('marketing.home', [
            'seo' => $seo,
            'plans' => $this->plans(),
            'stats' => $this->stats(),
            'reviews' => $this->featuredReviews(),
        ]);
    }

    public function features(string $locale): View
    {
        $seo = Seo::make(__('marketing.features.meta_title'), __('marketing.features.meta_description'))
            ->breadcrumbs([[__('marketing.nav.product'), route('marketing.features.index')]]);

        return view('marketing.features.index', ['seo' => $seo]);
    }

    public function feature(string $locale, string $module): View
    {
        abort_unless(array_key_exists($module, Catalog::FEATURES), 404);
        $key = 'marketing.features.items.'.$module;
        $faq = $this->pairs(__($key.'.faq'));
        $seo = Seo::make(__($key.'.meta_title'), __($key.'.lead'))
            ->breadcrumbs([[__('marketing.nav.product'), route('marketing.features.index')], [__($key.'.name'), url()->current()]])
            ->faq($faq);

        return view('marketing.features.show', ['seo' => $seo, 'module' => $module, 'icon' => Catalog::FEATURES[$module], 'faq' => $faq]);
    }

    public function solution(string $locale, string $segment): View
    {
        abort_unless(array_key_exists($segment, Catalog::SOLUTIONS), 404);
        $key = 'marketing.solutions.items.'.$segment;
        $seo = Seo::make(__($key.'.meta_title'), __($key.'.lead'))
            ->breadcrumbs([[__($key.'.name'), url()->current()]]);

        return view('marketing.solutions.show', ['seo' => $seo, 'segment' => $segment, 'icon' => Catalog::SOLUTIONS[$segment]]);
    }

    public function pricing(string $locale): View
    {
        $faq = array_map(fn ($k) => [__('marketing.pricing.faq.'.$k.'.q'), __('marketing.pricing.faq.'.$k.'.a')], Catalog::PRICING_FAQ);
        $seo = Seo::make(__('marketing.pricing.meta_title'), __('marketing.pricing.meta_description'))
            ->breadcrumbs([[__('marketing.nav.pricing'), url()->current()]])
            ->schema($this->softwareApplication())
            ->faq($faq);

        return view('marketing.pricing', [
            'seo' => $seo,
            'plans' => $this->plans(),
            'faq' => $faq,
            'currency' => Catalog::defaultCurrency($locale),
        ]);
    }

    public function compare(string $locale, string $competitor): View
    {
        abort_unless(array_key_exists($competitor, Catalog::COMPETITORS), 404);
        $name = Catalog::COMPETITORS[$competitor];
        $seo = Seo::make(__('marketing.compare.meta.'.$competitor), __('marketing.compare.meta_description'))
            ->breadcrumbs([[__('marketing.compare.crumb'), url()->current()]]);

        return view('marketing.compare', [
            'seo' => $seo,
            'competitor' => $competitor,
            'name' => $name,
            'matrix' => Catalog::COMPARE_MATRIX[$competitor],
        ]);
    }

    public function switch(string $locale): View
    {
        $faq = $this->pairs(__('marketing.switch.faq'));
        $seo = Seo::make(__('marketing.switch.meta_title'), __('marketing.switch.meta_description'))
            ->breadcrumbs([[__('marketing.nav.res.switch'), url()->current()]])
            ->faq($faq);

        return view('marketing.switch', ['seo' => $seo, 'faq' => $faq]);
    }

    public function about(string $locale): View
    {
        $seo = Seo::make(__('marketing.about.meta_title'), __('marketing.about.meta_description'))
            ->breadcrumbs([[__('marketing.footer.about'), url()->current()]])
            ->type('website');

        return view('marketing.about', ['seo' => $seo]);
    }

    public function contact(Request $request, string $locale): View
    {
        $seo = Seo::make(__('marketing.contact.meta_title'), __('marketing.contact.meta_description'))
            ->breadcrumbs([[__('marketing.footer.contact'), url()->current()]]);

        return view('marketing.contact', ['seo' => $seo, 'sent' => $request->boolean('sent')]);
    }

    public function contactSubmit(Request $request, string $locale): RedirectResponse
    {
        if (filled($request->input('website'))) {
            // Honeypot filled: silently accept without storing.
            return redirect()->route('marketing.contact', ['locale' => $locale, 'sent' => 1]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'topic' => ['required', 'in:demo,sales,support,migration,press,partnership'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'consent' => ['accepted'],
        ]);

        MarketingLead::create([
            'email' => strtolower($data['email']),
            'kind' => $data['topic'] === 'demo' ? 'demo' : 'contact',
            'resource' => $data['topic'],
            'locale' => $locale,
            'consent' => true,
        ]);

        Mail::raw(
            "{$data['name']} <{$data['email']}>\n".__('marketing.contact.topics.'.$data['topic'])." · {$locale}\n\n{$data['message']}",
            fn ($m) => $m->to(config('brand.support_email'))->replyTo($data['email'], $data['name'])->subject('['.config('brand.name').'] '.__('marketing.contact.topics.'.$data['topic']).' — '.$data['name']),
        );

        return redirect()->route('marketing.contact', ['locale' => $locale, 'sent' => 1]);
    }

    public function legal(string $locale, string $page): View
    {
        abort_unless(in_array($page, Catalog::LEGAL, true), 404);
        $seo = Seo::make(__('marketing.legal.'.$page.'.title'), __('marketing.legal.'.$page.'.summary'))
            ->breadcrumbs([[__('marketing.legal.'.$page.'.title'), url()->current()]]);

        return view('marketing.legal', ['seo' => $seo, 'page' => $page]);
    }

    public function calculators(string $locale): View
    {
        $seo = Seo::make(__('marketing.calculators.meta_title'), __('marketing.calculators.meta_description'))
            ->breadcrumbs([[__('marketing.nav.res.calculators'), url()->current()]]);

        return view('marketing.calculators.index', ['seo' => $seo]);
    }

    public function calculator(string $locale, string $calculator): View
    {
        abort_unless(array_key_exists($calculator, Catalog::CALCULATORS), 404);
        $key = 'marketing.calculators.items.'.$calculator;
        $faq = $this->pairs(__($key.'.faq'));
        $steps = (array) __($key.'.steps');
        $seo = Seo::make(__($key.'.meta_title'), __($key.'.lead'))
            ->breadcrumbs([[__('marketing.nav.res.calculators'), route('marketing.calculators.index')], [__($key.'.name'), url()->current()]])
            ->schema([
                '@type' => 'HowTo',
                'name' => __($key.'.how_title'),
                'description' => __($key.'.lead'),
                'step' => array_values(array_map(fn ($s, $i) => ['@type' => 'HowToStep', 'position' => $i + 1, 'text' => $s], $steps, array_keys($steps))),
            ])
            ->schema([
                '@type' => 'WebApplication',
                'name' => __($key.'.name'),
                'applicationCategory' => 'HealthApplication',
                'operatingSystem' => 'Web',
                'url' => url()->current(),
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
            ])
            ->faq($faq);

        return view('marketing.calculators.show', [
            'seo' => $seo,
            'calculator' => $calculator,
            'alpine' => Catalog::CALCULATORS[$calculator],
            'faq' => $faq,
            'steps' => $steps,
        ]);
    }

    /** @return Collection<int, SaasPlan> */
    private function plans(): Collection
    {
        return Cache::remember('marketing:plans', 300, fn () => SaasPlan::query()->where('is_public', true)->orderBy('sort')->get());
    }

    /** @return array{exercises: int, coaches: int} */
    private function stats(): array
    {
        return Cache::remember('marketing:stats', 600, fn () => [
            'exercises' => Exercise::query()->withoutGlobalScopes()->whereNull('tenant_id')->where('is_published', true)->whereNull('deleted_at')->count(),
            'coaches' => CoachProfile::query()->withoutGlobalScopes()->where('is_published', true)->count(),
        ]);
    }

    /** Real, approved 5★/4★ client reviews of published coaches (never invented). */
    private function featuredReviews(): Collection
    {
        return Review::query()->withoutGlobalScopes()
            ->where('status', 'approved')->where('rating', '>=', 4)->whereNotNull('body')
            ->whereHas('coachProfile', fn ($q) => $q->withoutGlobalScopes()->where('is_published', true))
            ->with(['coachProfile' => fn ($q) => $q->withoutGlobalScopes()->with('user:id,name,avatar_path'), 'client:id,name'])
            ->latest()->limit(3)->get();
    }

    /** SoftwareApplication with real offers from saas_plans. No aggregateRating (no verified product reviews yet). */
    private function softwareApplication(): array
    {
        $offers = $this->plans()->map(fn (SaasPlan $p) => [
            '@type' => 'Offer',
            'name' => $p->translate('name'),
            'price' => number_format($p->monthly_price_minor / 100, 2, '.', ''),
            'priceCurrency' => $p->currency,
            'priceSpecification' => ['@type' => 'UnitPriceSpecification', 'price' => number_format($p->monthly_price_minor / 100, 2, '.', ''), 'priceCurrency' => $p->currency, 'unitCode' => 'MON'],
            'url' => route('marketing.pricing'),
        ])->values()->all();

        return [
            '@type' => 'SoftwareApplication',
            'name' => config('brand.name'),
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'Personal training software',
            'operatingSystem' => 'Web, iOS, Android',
            'description' => __('marketing.home.meta_description'),
            'url' => route('marketing.home'),
            'inLanguage' => ['en', 'ar', 'fr', 'es'],
            'offers' => $offers,
        ];
    }

    /** @return list<array{0: string, 1: string}> */
    private function pairs(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(fn ($qa) => [(string) ($qa['q'] ?? ''), (string) ($qa['a'] ?? '')], $items));
    }
}
