<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\ProgramAssignment;

/**
 * Renders multilingual message bodies ({locale: text}) for a recipient:
 * picks the recipient's locale (falling back to the tenant locale, then
 * English, then any) and substitutes {first_name}, {name}, {program},
 * {coach_name} and {business}.
 */
final class TemplateRenderer
{
    public const VARIABLES = ['{first_name}', '{name}', '{program}', '{coach_name}', '{business}'];

    /**
     * @param  array<string, string>|string  $body
     * @param  array<string, string>  $extra  additional {key} => value replacements
     */
    public function render(array|string $body, User $recipient, Tenant $tenant, array $extra = []): string
    {
        $locale = $recipient->preferredLocale();
        $text = is_string($body)
            ? $body
            : (string) ($body[$locale] ?? $body[$tenant->locale] ?? $body['en'] ?? (array_values(array_filter($body))[0] ?? ''));

        $replacements = ['{first_name}' => $recipient->firstName(), '{name}' => $recipient->name, '{business}' => (string) $tenant->name];

        if (str_contains($text, '{program}')) {
            $assignment = ProgramAssignment::withoutGlobalScopes()->with(['program' => fn ($q) => $q->withoutGlobalScopes()])
                ->where('tenant_id', $tenant->id)->where('client_id', $recipient->id)->where('status', 'active')
                ->latest('start_date')->first();
            $replacements['{program}'] = (string) ($assignment?->program?->translate('name', $locale) ?? '');
        }
        if (str_contains($text, '{coach_name}')) {
            $profile = ClientProfile::withoutGlobalScopes()->with('coach')->where('tenant_id', $tenant->id)->where('user_id', $recipient->id)->first();
            $replacements['{coach_name}'] = (string) ($profile?->coach?->name ?? '');
        }
        foreach ($extra as $key => $value) {
            $replacements['{'.trim($key, '{}').'}'] = $value;
        }

        return trim(strtr($text, $replacements));
    }

    /**
     * Normalizes a body input (string or {locale: text}) to {locale: text}.
     *
     * @param  array<string, string>|string  $input
     * @return array<string, string>
     */
    public function normalize(array|string $input, string $defaultLocale): array
    {
        if (is_string($input)) {
            return [$defaultLocale => $input];
        }

        return array_filter(array_intersect_key($input, array_flip(['en', 'ar', 'fr', 'es'])), fn ($v) => is_string($v) && trim($v) !== '');
    }
}
