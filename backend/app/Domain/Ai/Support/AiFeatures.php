<?php

namespace App\Domain\Ai\Support;

/** Catalogue of copilot features and who they serve. */
final class AiFeatures
{
    /** Coach-facing features (plan feature "ai_copilot"; drafts need approval). */
    public const COACH = ['program', 'progression', 'meal_plan', 'checkin_summary', 'reply_draft', 'thread_summary', 'churn', 'business_ask', 'content'];

    /** Client-side tools the tenant enables (tenants.settings.ai.*). */
    public const CLIENT = ['photo_food', 'form_check', 'coach_chat', 'starter_plan', 'weekly_recap'];

    /** Client tool => tenants.settings.ai key and its default for business tenants. */
    public const CLIENT_TOOL_SETTINGS = [
        'photo_food' => ['client_photo_logging', true],
        'form_check' => ['client_form_check', true],
        'coach_chat' => ['self_guided_chat', false],
        'starter_plan' => ['self_guided_chat', false],
        'weekly_recap' => ['client_weekly_recap', true],
    ];

    /** Features whose approved output can be applied as a real artifact. */
    public const APPLICABLE = ['program', 'meal_plan', 'progression'];

    /** @return list<string> */
    public static function all(): array
    {
        return [...self::COACH, ...self::CLIENT];
    }

    public static function isCoach(string $feature): bool
    {
        return in_array($feature, self::COACH, true);
    }

    public static function isClient(string $feature): bool
    {
        return in_array($feature, self::CLIENT, true);
    }
}
