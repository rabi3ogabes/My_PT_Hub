<?php

namespace App\Domain\Workouts\Support;

/**
 * Normalises translatable input: a plain string is stored under the current
 * locale (keeping other locales), an object {en, ar, fr, es} is merged.
 */
final class Translatable
{
    public const LOCALES = ['en', 'ar', 'fr', 'es'];

    /**
     * @param  array<string, mixed>|null  $existing
     * @return array<string, mixed>|null
     */
    public static function merge(mixed $value, ?array $existing = null): ?array
    {
        if ($value === null) {
            return $existing;
        }
        $current = $existing ?? [];
        if (is_string($value)) {
            $current[app()->getLocale()] = $value;

            return $current;
        }
        if (is_array($value)) {
            if (array_is_list($value)) {
                // A list (e.g. instruction steps) for the current locale.
                $current[app()->getLocale()] = array_values(array_filter($value, fn ($v) => is_string($v) && $v !== ''));

                return $current;
            }
            foreach ($value as $locale => $text) {
                if (in_array($locale, self::LOCALES, true)) {
                    $current[$locale] = $text;
                }
            }

            return $current;
        }

        return $current;
    }

    /** Validation rule closure accepting a string or a {locale: string} object. */
    public static function rule(int $max = 190, bool $list = false): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($max, $list): void {
            $isText = fn ($v) => is_string($v) && mb_strlen($v) <= $max;
            $isList = fn ($v) => is_array($v) && array_is_list($v) && count($v) <= 30 && collect($v)->every($isText);
            $ok = $list
                ? ($isList($value) || (is_array($value) && ! array_is_list($value) && collect($value)->every(fn ($v, $k) => in_array($k, self::LOCALES, true) && ($isList($v) || $v === null))))
                : ($isText($value) || (is_array($value) && ! array_is_list($value) && collect($value)->every(fn ($v, $k) => in_array($k, self::LOCALES, true) && ($isText($v) || $v === null))));
            if (! $ok) {
                $fail(__('workouts.invalid_translation'));
            }
        };
    }
}
