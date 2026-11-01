<?php

namespace App\Domain\Ai\Support;

/**
 * Removes direct identifiers before anything is sent to a provider. Prompts
 * are built with first names only; this is the safety net for free text
 * (messages, check-in answers) that may contain contact details.
 */
final class PiiRedactor
{
    public static function redact(string $text): string
    {
        $text = (string) preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', '[email]', $text);
        $text = (string) preg_replace('/https?:\/\/\S+/iu', '[link]', $text);
        $text = (string) preg_replace('/\b(?:\d[ \-]?){13,19}\b/u', '[card]', $text);
        // Phone numbers: optional +, 8+ digits allowing spaces, dots, dashes and parentheses.
        $text = (string) preg_replace_callback('/(?<![\p{L}\d])\+?\(?\d{1,4}\)?(?:[\s.\-]?\(?\d{2,4}\)?){1,5}(?![\p{L}\d])/u', function (array $m): string {
            $match = $m[0];
            $digits = (int) preg_match_all('/\d/', $match);
            $groups = count(preg_split('/[\s.\-]+/', trim($match)) ?: []);
            $looksLikePhone = str_starts_with($match, '+') || str_starts_with($match, '(') || str_starts_with($match, '0') || $groups <= 2;

            return $digits >= 8 && $looksLikePhone ? '[phone]' : $match;
        }, $text);

        return $text;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public static function redactArray(array $data): array
    {
        array_walk_recursive($data, function (&$value): void {
            if (is_string($value)) {
                $value = self::redact($value);
            }
        });

        return $data;
    }
}
