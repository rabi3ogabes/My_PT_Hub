<?php

namespace App\Domain\Workouts\Support;

/**
 * Normalised, locale-agnostic search haystack for exercises (names + aliases in
 * every locale). Latin text is lower-cased and accent-stripped ("Développé" →
 * "developpe"); Arabic loses tashkeel/tatweel and letter variants are unified
 * (أ إ آ → ا, ى → ي, ة → ه) so "ضغط صدر دمبل" matches however it is typed.
 */
final class SearchText
{
    private const ARABIC_MAP = [
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
        'ى' => 'ي', 'ئ' => 'ي', 'ؤ' => 'و', 'ة' => 'ه',
    ];

    public static function normalize(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
        $text = strtr($text, self::ARABIC_MAP);
        if (class_exists(\Transliterator::class)) {
            $latin = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($latin !== null) {
                $text = (string) $latin->transliterate($text);
            }
        }
        $text = str_replace(['œ', 'æ', 'ß', '’', "'", '-', '_', '/'], ['oe', 'ae', 'ss', ' ', ' ', ' ', ' ', ' '], $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * @param  mixed  ...$sources  translation maps, lists of strings or strings
     */
    public static function build(mixed ...$sources): string
    {
        $parts = [];
        array_walk_recursive($sources, function ($value) use (&$parts): void {
            if (is_string($value) && $value !== '') {
                $parts[] = self::normalize($value);
            }
        });

        return '|'.implode('|', array_values(array_unique(array_filter($parts)))).'|';
    }

    /** Escapes LIKE wildcards in a user term. */
    public static function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }
}
