<?php

namespace App\Domain\Ai\Support;

/** Language-agnostic normalisation for matching (accents, Arabic forms, digits). */
final class TextNormalizer
{
    private const ARABIC_MAP = [
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ى' => 'ي', 'ئ' => 'ي', 'ؤ' => 'و', 'ة' => 'ه',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    private const STOPWORDS = ['the', 'a', 'an', 'of', 'with', 'and', 'de', 'la', 'le', 'les', 'du', 'des', 'el', 'los', 'las', 'con', 'y', 'et', 'au', 'aux', 'en'];

    public static function normalize(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
        $text = strtr($text, self::ARABIC_MAP);
        $translit = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
        if ($translit !== null) {
            $text = (string) $translit->transliterate($text);
        }
        $text = str_replace(['œ', 'æ', 'ß', '’', "'", '-', '_', '/'], ['oe', 'ae', 'ss', ' ', ' ', ' ', ' ', ' '], $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /** @return list<string> */
    public static function tokens(?string $text): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', self::normalize($text)) ?: [];
        $out = [];
        foreach ($parts as $p) {
            if ($p !== '' && mb_strlen($p) > 1 && ! in_array($p, self::STOPWORDS, true)) {
                $out[] = self::stem($p);
            }
        }

        return array_values(array_unique($out));
    }

    /** Token-set similarity in [0, 1] (Dice coefficient). */
    public static function similarity(string $a, string $b): float
    {
        $ta = self::tokens($a);
        $tb = self::tokens($b);
        if ($ta === [] || $tb === []) {
            return 0.0;
        }

        return 2 * count(array_intersect($ta, $tb)) / (count($ta) + count($tb));
    }

    private static function stem(string $token): string
    {
        // Light plural stripping for Latin scripts (dumbbells -> dumbbell, curls -> curl).
        if (mb_strlen($token) > 3 && preg_match('/^[a-z]+$/', $token)) {
            if (str_ends_with($token, 'ies')) {
                return substr($token, 0, -3).'y';
            }
            if (str_ends_with($token, 's') && ! str_ends_with($token, 'ss')) {
                return substr($token, 0, -1);
            }
        }

        return $token;
    }
}
