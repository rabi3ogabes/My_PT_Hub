<?php

namespace App\Http\Api\V1\Support;

/** Accepts a plain string (stored under the current locale) or a {en, ar, fr, es} object. */
final class TranslatableInput
{
    public const LOCALES = ['en', 'ar', 'fr', 'es'];

    /** @return array<string, mixed> */
    public static function rules(string $field, bool $required = true, int $max = 190): array
    {
        return [
            $field => [$required ? 'required' : 'nullable', function (string $attribute, mixed $value, \Closure $fail) use ($max) {
                if (is_string($value)) {
                    if (mb_strlen($value) > $max) {
                        $fail(__('validation.max.string', ['attribute' => $attribute, 'max' => $max]));
                    }

                    return;
                }
                if (! is_array($value) || $value === [] || array_diff(array_keys($value), self::LOCALES)) {
                    $fail(__('validation.array', ['attribute' => $attribute]));

                    return;
                }
                foreach ($value as $v) {
                    if (! is_string($v) && ! is_array($v) && $v !== null) {
                        $fail(__('validation.string', ['attribute' => $attribute]));
                    }
                }
            }],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $existing
     * @return array<string, mixed>|null
     */
    public static function value(mixed $input, ?array $existing = null): ?array
    {
        if ($input === null) {
            return null;
        }
        if (is_string($input)) {
            return array_merge($existing ?? [], [app()->getLocale() => $input]);
        }

        return array_filter(array_intersect_key((array) $input, array_flip(self::LOCALES)), fn ($v) => $v !== null && $v !== '');
    }
}
