<?php

namespace App\Providers\Filament;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Tenancy\Models\FeatureFlag;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Super-admin control room. Only users with is_super_admin may enter
 * (User::canAccessPanel). Admins see every tenant: resources strip the
 * tenant / shared-library global scopes from their queries.
 */
class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // PlanGate caches flags (60 s) and plans (300 s); admin edits must apply immediately.
        $forgetFlag = function (FeatureFlag $flag): void {
            Cache::forget('flag:'.$flag->getAttribute('key'));
            if ($flag->getOriginal('key')) {
                Cache::forget('flag:'.$flag->getOriginal('key'));
            }
        };
        FeatureFlag::saved($forgetFlag);
        FeatureFlag::deleted($forgetFlag);

        $forgetPlan = function (SaasPlan $plan): void {
            Cache::forget('plan:'.$plan->getAttribute('code'));
            $original = $plan->getOriginal('features');
            $original = is_array($original) ? $original : (json_decode((string) $original, true) ?: []);
            foreach (array_unique(array_merge($plan->features ?? [], $original)) as $feature) {
                Cache::forget('plan-feature:'.$feature);
            }
        };
        SaasPlan::saved($forgetPlan);
        SaasPlan::deleted($forgetPlan);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn (): string => config('brand.name').' Admin')
            ->colors([
                'primary' => Color::hex('#D4AF6A'),
                'gray' => Color::Stone,
                'success' => Color::hex('#3DBE8B'),
                'warning' => Color::hex('#F2B544'),
                'danger' => Color::hex('#EF5A5A'),
                'info' => Color::hex('#5B8DEF'),
            ])
            ->font('Manrope')
            ->monoFont('JetBrains Mono')
            ->darkMode()
            ->defaultThemeMode(ThemeMode::Dark)
            ->spa()
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make(fn (): string => __('Platform')),
                NavigationGroup::make(fn (): string => __('Content')),
                NavigationGroup::make(fn (): string => __('Billing')),
                NavigationGroup::make(fn (): string => __('Operations')),
                NavigationGroup::make(fn (): string => __('Marketing')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
