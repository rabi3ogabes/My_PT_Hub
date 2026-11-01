<?php

namespace App\Filament\Support;

/** Normalises {locale: value} JSON columns between the database and admin forms. */
final class TranslatableData
{
    /**
     * Ensures per-locale values are lists (repeaters/tags expect arrays; legacy rows may hold a string).
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $attributes
     * @return array<string, mixed>
     */
    public static function listify(array $data, array $attributes): array
    {
        foreach ($attributes as $attribute) {
            if (! is_array($data[$attribute] ?? null)) {
                continue;
            }
            foreach ($data[$attribute] as $locale => $value) {
                if (is_string($value)) {
                    $data[$attribute][$locale] = array_values(array_filter(preg_split('/\R/', $value) ?: [], fn (string $l): bool => trim($l) !== ''));
                } elseif ($value === null) {
                    $data[$attribute][$locale] = [];
                }
            }
        }

        return $data;
    }

    /**
     * Drops empty locales so missing translations fall back instead of rendering blanks.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $attributes
     * @return array<string, mixed>
     */
    public static function clean(array $data, array $attributes): array
    {
        foreach ($attributes as $attribute) {
            if (! array_key_exists($attribute, $data)) {
                continue;
            }
            $value = is_array($data[$attribute]) ? $data[$attribute] : [];
            $value = array_filter($value, function ($v): bool {
                if (is_array($v)) {
                    return count(array_filter($v, fn ($i): bool => filled($i))) > 0;
                }

                return filled($v);
            });
            $data[$attribute] = $value === [] ? null : array_map(fn ($v) => is_array($v) ? array_values(array_filter($v, fn ($i): bool => filled($i))) : $v, $value);
        }

        return $data;
    }
}
