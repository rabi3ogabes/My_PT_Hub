<?php

namespace App\Filament\Support;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/** Builds per-locale tabs for JSON translatable columns ({en, ar, fr, es}). */
final class Locales
{
    /** @return list<string> */
    public static function all(): array
    {
        return array_values(config('brand.locales', ['en', 'ar', 'fr', 'es']));
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::all())->mapWithKeys(fn (string $l): array => [$l => strtoupper($l)])->all();
    }

    /**
     * @param  Closure(string $locale): array<int, mixed>  $fields  receives the locale, returns the tab's components
     */
    public static function tabs(string $id, Closure $fields): Tabs
    {
        return Tabs::make($id)
            ->tabs(collect(self::all())->map(
                fn (string $locale): Tab => Tab::make(strtoupper($locale))
                    ->schema($fields($locale))
                    ->extraAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr'])
            )->all())
            ->columnSpanFull();
    }
}
