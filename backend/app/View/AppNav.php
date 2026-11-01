<?php

namespace App\View;

use Illuminate\Support\Facades\Route;

/**
 * Coach dashboard navigation. A module links to its named route as soon as the
 * module ships it; until then the item renders disabled with a "soon" pill.
 */
final class AppNav
{
    /** @var array<string, list<array{key: string, icon: string, routes: list<string>}>> */
    public const GROUPS = [
        'coaching' => [
            ['key' => 'dashboard', 'icon' => 'home', 'routes' => ['app.dashboard']],
            ['key' => 'clients', 'icon' => 'users', 'routes' => ['app.clients.index', 'app.clients']],
            ['key' => 'programs', 'icon' => 'layers', 'routes' => ['app.programs.index', 'app.programs']],
            ['key' => 'workouts', 'icon' => 'dumbbell', 'routes' => ['app.workouts.index', 'app.workouts']],
            ['key' => 'exercises', 'icon' => 'bolt', 'routes' => ['app.exercises.index', 'app.exercises']],
            ['key' => 'nutrition', 'icon' => 'leaf', 'routes' => ['app.nutrition.index', 'app.nutrition']],
            ['key' => 'habits', 'icon' => 'check-circle', 'routes' => ['app.habits.index', 'app.habits']],
            ['key' => 'checkins', 'icon' => 'clipboard-check', 'routes' => ['app.checkins.index', 'app.checkins']],
        ],
        'engage' => [
            ['key' => 'calendar', 'icon' => 'calendar', 'routes' => ['app.calendar.index', 'app.calendar']],
            ['key' => 'messages', 'icon' => 'chat', 'routes' => ['app.messages.index', 'app.messages']],
            ['key' => 'community', 'icon' => 'globe', 'routes' => ['app.community.index', 'app.community']],
        ],
        'business' => [
            ['key' => 'payments', 'icon' => 'credit-card', 'routes' => ['app.payments.index', 'app.payments']],
            ['key' => 'products', 'icon' => 'tag', 'routes' => ['app.products.index', 'app.products']],
            ['key' => 'analytics', 'icon' => 'chart', 'routes' => ['app.analytics.index', 'app.analytics']],
            ['key' => 'crm', 'icon' => 'funnel', 'routes' => ['app.crm.index', 'app.crm']],
            ['key' => 'marketing', 'icon' => 'megaphone', 'routes' => ['app.marketing.index', 'app.marketing']],
        ],
        'workspace' => [
            ['key' => 'team', 'icon' => 'user-group', 'routes' => ['app.settings.team']],
            ['key' => 'settings', 'icon' => 'cog', 'routes' => ['app.settings.profile']],
            ['key' => 'billing', 'icon' => 'receipt', 'routes' => ['app.settings.billing']],
        ],
    ];

    /**
     * @return list<array{group: string, items: list<array{key: string, label: string, icon: string, href: string, active: bool, soon: bool}>}>
     */
    public static function groups(): array
    {
        $current = Route::currentRouteName() ?? '';
        $out = [];
        foreach (self::GROUPS as $group => $items) {
            $rows = [];
            foreach ($items as $item) {
                $name = collect($item['routes'])->first(fn ($r) => Route::has($r));
                $prefix = $name ? preg_replace('/\.(index|show|profile|team|billing|dashboard)$/', '', $name) : null;
                $rows[] = [
                    'key' => $item['key'],
                    'label' => __('web.nav.'.$item['key']),
                    'icon' => $item['icon'],
                    'href' => $name ? route($name) : '#',
                    'active' => $name !== null && ($current === $name || ($prefix !== 'app' && $prefix !== 'app.settings' && str_starts_with($current, $prefix.'.'))),
                    'soon' => $name === null,
                ];
            }
            $out[] = ['group' => __('web.nav.group_'.$group), 'items' => $rows];
        }

        return $out;
    }

    /** Flat list for the ⌘K command palette. @return list<array{label: string, group: string, href: string}> */
    public static function palette(): array
    {
        $items = [];
        foreach (self::groups() as $group) {
            foreach ($group['items'] as $item) {
                if (! $item['soon']) {
                    $items[] = ['label' => $item['label'], 'group' => $group['group'], 'href' => $item['href']];
                }
            }
        }
        foreach (['app.settings.business' => 'web.settings.business', 'app.settings.notifications' => 'web.settings.notifications', 'app.onboarding' => 'web.onboarding.title'] as $route => $key) {
            if (Route::has($route)) {
                $items[] = ['label' => __($key), 'group' => __('web.nav.group_workspace'), 'href' => route($route)];
            }
        }

        return $items;
    }
}
