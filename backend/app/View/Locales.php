<?php

namespace App\View;

use App\Http\Middleware\SetLocale;

/** Locale metadata for switchers, <html lang dir> and Open Graph. */
final class Locales
{
    /** @var array<string, array{name: string, og: string}> */
    public const META = [
        'en' => ['name' => 'English', 'og' => 'en_US'],
        'ar' => ['name' => 'العربية', 'og' => 'ar_AR'],
        'fr' => ['name' => 'Français', 'og' => 'fr_FR'],
        'es' => ['name' => 'Español', 'og' => 'es_ES'],
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return SetLocale::SUPPORTED;
    }

    public static function dir(?string $locale = null): string
    {
        return SetLocale::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function name(string $locale): string
    {
        return self::META[$locale]['name'] ?? strtoupper($locale);
    }

    public static function og(?string $locale = null): string
    {
        return self::META[$locale ?? app()->getLocale()]['og'] ?? 'en_US';
    }

    /** Swap the leading /{locale} segment of a path, used by the switcher to preserve the page. */
    public static function swapPath(string $path, string $locale): string
    {
        $segments = explode('/', ltrim($path, '/'));
        if (in_array($segments[0] ?? '', self::all(), true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        return '/'.rtrim(implode('/', $segments), '/');
    }
}
