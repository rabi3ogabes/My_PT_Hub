<?php

namespace App\Domain\Nutrition\Support;

/**
 * Builds the normalised, locale-agnostic search haystack for foods and recipes.
 * Latin text is lower-cased and stripped of accents ("Crêpe" → "crepe");
 * Arabic text is stripped of tashkeel/tatweel and its letter variants are
 * unified (أ إ آ → ا, ى → ي, ة → ه) so users find "كبسة" however it is typed.
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
        // Arabic diacritics (fathatan..sukun, superscript alef) and tatweel.
        $text = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
        $text = strtr($text, self::ARABIC_MAP);
        if (class_exists(\Transliterator::class)) {
            $latin = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($latin !== null) {
                $text = (string) $latin->transliterate($text);
            }
        }
        $text = str_replace(['œ', 'æ', 'ß', '’', "'"], ['oe', 'ae', 'ss', ' ', ' '], $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * @param  array<int|string, mixed>  ...$sources  translation maps or lists of strings
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

    /** A LIKE pattern for a normalised term (user wildcards are dropped). */
    public static function likeTerm(string $term): string
    {
        return '%'.trim(str_replace(['%', '_', '\\'], ' ', self::normalize($term))).'%';
    }
}
