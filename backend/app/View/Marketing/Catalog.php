<?php

namespace App\View\Marketing;

/**
 * Structure of the public site (slugs, icons, comparison matrices, display currencies).
 * Every user-facing string lives in lang/{locale}/marketing.php keyed by these slugs.
 */
final class Catalog
{
    /** @var array<string, string> feature slug => icon */
    public const FEATURES = [
        'workouts' => 'dumbbell',
        'nutrition' => 'leaf',
        'ai-copilot' => 'sparkles',
        'check-ins' => 'clipboard-check',
        'payments' => 'credit-card',
        'bookings' => 'calendar',
        'community' => 'users',
        'white-label' => 'swatch',
        'integrations' => 'puzzle',
    ];

    /** @var array<string, string> solution slug => icon */
    public const SOLUTIONS = [
        'personal-trainers' => 'dumbbell',
        'online-coaches' => 'globe',
        'nutritionists' => 'leaf',
        'gyms-studios' => 'building',
        'physiotherapists' => 'heart-pulse',
        'crossfit-hyrox' => 'bolt',
        'womens-coaches' => 'sparkles',
    ];

    /** @var array<string, string> competitor slug => display name (nominative, comparative use only) */
    public const COMPETITORS = [
        'my-pt-hub' => 'My PT Hub',
        'trainerize' => 'Trainerize',
        'everfit' => 'Everfit',
        'pt-distinction' => 'PT Distinction',
        'truecoach' => 'TrueCoach',
    ];

    /** Comparison rows. Values: yes | no | partial | addon | varies. Reviewed against public pricing pages. */
    public const COMPARE_ROWS = ['ai_included', 'arabic_rtl', 'french_spanish', 'two_click_cancel', 'monthly_default', 'nutrition', 'branded_app', 'offline_logging', 'gulf_gateways', 'community', 'free_migration'];

    /** @var array<string, array<string, string>> */
    public const COMPARE_MATRIX = [
        'my-pt-hub' => ['ai_included' => 'varies', 'arabic_rtl' => 'no', 'french_spanish' => 'varies', 'two_click_cancel' => 'varies', 'monthly_default' => 'yes', 'nutrition' => 'yes', 'branded_app' => 'addon', 'offline_logging' => 'varies', 'gulf_gateways' => 'no', 'community' => 'varies', 'free_migration' => 'varies'],
        'trainerize' => ['ai_included' => 'varies', 'arabic_rtl' => 'no', 'french_spanish' => 'partial', 'two_click_cancel' => 'varies', 'monthly_default' => 'yes', 'nutrition' => 'addon', 'branded_app' => 'addon', 'offline_logging' => 'varies', 'gulf_gateways' => 'no', 'community' => 'yes', 'free_migration' => 'varies'],
        'everfit' => ['ai_included' => 'varies', 'arabic_rtl' => 'no', 'french_spanish' => 'varies', 'two_click_cancel' => 'varies', 'monthly_default' => 'yes', 'nutrition' => 'yes', 'branded_app' => 'addon', 'offline_logging' => 'varies', 'gulf_gateways' => 'no', 'community' => 'yes', 'free_migration' => 'varies'],
        'pt-distinction' => ['ai_included' => 'varies', 'arabic_rtl' => 'no', 'french_spanish' => 'varies', 'two_click_cancel' => 'varies', 'monthly_default' => 'yes', 'nutrition' => 'yes', 'branded_app' => 'addon', 'offline_logging' => 'varies', 'gulf_gateways' => 'no', 'community' => 'varies', 'free_migration' => 'varies'],
        'truecoach' => ['ai_included' => 'varies', 'arabic_rtl' => 'no', 'french_spanish' => 'varies', 'two_click_cancel' => 'varies', 'monthly_default' => 'yes', 'nutrition' => 'partial', 'branded_app' => 'varies', 'offline_logging' => 'varies', 'gulf_gateways' => 'no', 'community' => 'varies', 'free_migration' => 'varies'],
    ];

    public const COMPARE_REVIEWED = '2026-10-15';

    /** @var array<string, string> calculator slug => Alpine component */
    public const CALCULATORS = [
        'tdee' => 'tdee',
        'macros' => 'macros',
        'one-rep-max' => 'oneRepMax',
        'body-fat' => 'bodyFat',
        'bmi' => 'bmi',
        'protein' => 'protein',
        'water' => 'water',
        'pt-pricing' => 'ptPricing',
        'coach-earnings' => 'coachEarnings',
    ];

    /** @var list<string> */
    public const LEGAL = ['terms', 'privacy', 'cookies', 'refund', 'dpa'];

    /**
     * Display-only conversion from USD (billing currency). Gulf rates are fixed pegs;
     * EUR and MAD are indicative and labelled as such on the page.
     *
     * @var array<string, float>
     */
    public const CURRENCIES = [
        'USD' => 1.0,
        'QAR' => 3.64,
        'SAR' => 3.75,
        'AED' => 3.6725,
        'EUR' => 0.92,
        'MAD' => 9.95,
    ];

    /** Default display currency per locale. */
    public static function defaultCurrency(string $locale): string
    {
        return ['ar' => 'QAR', 'fr' => 'EUR', 'es' => 'EUR'][$locale] ?? 'USD';
    }

    /** Pricing FAQ keys (copy in marketing.pricing.faq). */
    public const PRICING_FAQ = ['trial', 'cancel', 'refund', 'card', 'currency', 'clients', 'switch', 'ai'];

    /** Featured cities for the coach directory (launch markets). */
    public const CITIES = ['doha', 'riyadh', 'dubai', 'paris', 'casablanca', 'madrid'];

    /** Static, indexable routes for the pages sitemap (route name => params). */
    public static function staticPages(): array
    {
        $pages = [
            ['marketing.home', []],
            ['marketing.pricing', []],
            ['marketing.features.index', []],
            ['marketing.switch', []],
            ['marketing.about', []],
            ['marketing.contact', []],
            ['marketing.calculators.index', []],
            ['marketing.exercises.index', []],
            ['marketing.templates.index', []],
            ['marketing.coaches.index', []],
            ['marketing.blog.index', []],
        ];
        foreach (array_keys(self::FEATURES) as $slug) {
            $pages[] = ['marketing.features.show', ['module' => $slug]];
        }
        foreach (array_keys(self::SOLUTIONS) as $slug) {
            $pages[] = ['marketing.solutions.show', ['segment' => $slug]];
        }
        foreach (array_keys(self::COMPETITORS) as $slug) {
            $pages[] = ['marketing.compare', ['competitor' => $slug]];
        }
        foreach (array_keys(self::CALCULATORS) as $slug) {
            $pages[] = ['marketing.calculators.show', ['calculator' => $slug]];
        }
        foreach (self::LEGAL as $slug) {
            $pages[] = ['marketing.legal', ['page' => $slug]];
        }

        return $pages;
    }
}
