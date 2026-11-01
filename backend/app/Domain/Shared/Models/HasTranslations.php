<?php

namespace App\Domain\Shared\Models;

/**
 * JSON-column translations: {"en": "...", "ar": "...", "fr": "...", "es": "..."}.
 * Declare `protected array $translatable = ['name', ...]` and cast them to array.
 */
trait HasTranslations
{
    public function translate(string $attribute, ?string $locale = null): ?string
    {
        $value = $this->getAttribute($attribute);
        if (! is_array($value)) {
            return $value === null ? null : (string) $value;
        }
        $locale ??= app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');
        $result = $value[$locale] ?? $value[$fallback] ?? (array_values(array_filter($value))[0] ?? null);

        return is_array($result) ? implode("\n", $result) : $result;
    }

    /** Returns the raw translation (may be an array, e.g. instruction steps). */
    public function translateRaw(string $attribute, ?string $locale = null): mixed
    {
        $value = $this->getAttribute($attribute);
        if (! is_array($value)) {
            return $value;
        }
        $locale ??= app()->getLocale();

        return $value[$locale] ?? $value[config('app.fallback_locale', 'en')] ?? null;
    }

    public function setTranslation(string $attribute, string $locale, mixed $value): static
    {
        $current = $this->getAttribute($attribute);
        $current = is_array($current) ? $current : [];
        $current[$locale] = $value;
        $this->setAttribute($attribute, $current);

        return $this;
    }

    /** @return list<string> */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }
}
