<?php

namespace App\Domain\Ai\Support;

/**
 * JSON schemas for every structured copilot output. They are validated
 * locally (SchemaValidator) and sent to providers' strict modes in reduced
 * form (SchemaValidator::forProvider()).
 */
final class Schemas
{
    public const LANGUAGES = ['en', 'ar', 'fr', 'es'];

    public const BLOCK_TYPES = ['straight', 'superset', 'giant', 'circuit', 'tabata', 'emom', 'amrap', 'interval', 'warmup', 'cooldown'];

    public const SET_TYPES = ['warmup', 'working', 'drop', 'failure', 'amrap'];

    public const GOALS = ['fat_loss', 'muscle_gain', 'strength', 'performance', 'health', 'general'];

    public const LEVELS = ['beginner', 'intermediate', 'advanced'];

    public const SLOTS = ['breakfast', 'lunch', 'dinner', 'snack', 'suhoor', 'iftar'];

    public const PROGRESSION_ACTIONS = ['increase_load', 'decrease_load', 'increase_volume', 'decrease_volume', 'hold', 'swap'];

    public const BUSINESS_TOOLS = ['unpaid_clients', 'best_retention_program', 'at_risk_clients', 'revenue', 'pending_checkins', 'unsupported'];

    public const CONTENT_TYPES = ['instagram_caption', 'program_description', 'sales_copy', 'email', 'story'];

    /** @return array<string, mixed> */
    private static function str(int $max = 2000, int $min = 0): array
    {
        return array_filter(['type' => 'string', 'maxLength' => $max, 'minLength' => $min ?: null], fn ($v) => $v !== null);
    }

    /** @return array<string, mixed> */
    private static function nullable(string $type, array $extra = []): array
    {
        return ['type' => [$type, 'null']] + $extra;
    }

    /**
     * @param  array<string, array<string, mixed>>  $properties
     * @return array<string, mixed>
     */
    private static function obj(array $properties): array
    {
        return ['type' => 'object', 'properties' => $properties, 'required' => array_keys($properties), 'additionalProperties' => false];
    }

    /** @return array<string, mixed> */
    private static function list(array $items, int $min = 0, ?int $max = null): array
    {
        return array_filter(['type' => 'array', 'items' => $items, 'minItems' => $min ?: null, 'maxItems' => $max], fn ($v) => $v !== null);
    }

    /** @return array<string, mixed> */
    public static function program(): array
    {
        $set = self::obj([
            'set_type' => ['type' => 'string', 'enum' => self::SET_TYPES],
            'reps_min' => self::nullable('integer', ['minimum' => 1, 'maximum' => 100]),
            'reps_max' => self::nullable('integer', ['minimum' => 1, 'maximum' => 100]),
            'rpe' => self::nullable('number', ['minimum' => 1, 'maximum' => 10]),
            'duration_seconds' => self::nullable('integer', ['minimum' => 5, 'maximum' => 3600]),
            'rest_seconds' => self::nullable('integer', ['minimum' => 0, 'maximum' => 600]),
        ]);
        $exercise = self::obj([
            'exercise_id' => self::str(40),
            'exercise_name' => self::str(160),
            'rest_seconds' => self::nullable('integer', ['minimum' => 0, 'maximum' => 600]),
            'tempo' => self::nullable('string', ['maxLength' => 16]),
            'notes' => self::nullable('string', ['maxLength' => 500]),
            'sets' => self::list($set, 1, 10),
        ]);
        $block = self::obj([
            'type' => ['type' => 'string', 'enum' => self::BLOCK_TYPES],
            'name' => self::nullable('string', ['maxLength' => 120]),
            'rounds' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20],
            'rest_seconds' => self::nullable('integer', ['minimum' => 0, 'maximum' => 600]),
            'exercises' => self::list($exercise, 1, 12),
        ]);

        return self::obj([
            'name' => self::str(120, 2),
            'description' => self::str(1500),
            'goal' => ['type' => 'string', 'enum' => self::GOALS],
            'level' => ['type' => 'string', 'enum' => self::LEVELS],
            'days_per_week' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 7],
            'weeks_count' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 16],
            'deload_week' => self::nullable('integer', ['minimum' => 1, 'maximum' => 16]),
            'progression' => self::obj([
                'type' => ['type' => 'string', 'enum' => ['linear_load', 'rep_progression', 'percent_week', 'rpe_based']],
                'increment_kg' => self::nullable('number', ['minimum' => 0, 'maximum' => 10]),
                'notes' => self::str(800),
            ]),
            'days' => self::list(self::obj([
                'day_number' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 7],
                'title' => self::str(120),
                'workout' => self::obj([
                    'name' => self::str(120, 2),
                    'estimated_minutes' => ['type' => 'integer', 'minimum' => 10, 'maximum' => 180],
                    'blocks' => self::list($block, 1, 10),
                ]),
            ]), 1, 7),
            'notes' => self::str(1500),
        ]);
    }

    /** @return array<string, mixed> */
    public static function progression(): array
    {
        $load = self::obj([
            'weight_kg' => self::nullable('number', ['minimum' => 0, 'maximum' => 500]),
            'reps' => self::nullable('integer', ['minimum' => 1, 'maximum' => 100]),
            'sets' => self::nullable('integer', ['minimum' => 1, 'maximum' => 12]),
        ]);

        return self::obj([
            'summary' => self::str(1500),
            'suggestions' => self::list(self::obj([
                'exercise_id' => self::str(40),
                'exercise_name' => self::str(160),
                'action' => ['type' => 'string', 'enum' => self::PROGRESSION_ACTIONS],
                'current' => $load,
                'proposed' => $load,
                'swap_exercise_id' => self::nullable('string', ['maxLength' => 40]),
                'rationale' => self::str(600),
            ]), 0, 40),
        ]);
    }

    /** @return array<string, mixed> */
    public static function mealPlan(): array
    {
        $item = self::obj([
            'food_id' => self::nullable('string', ['maxLength' => 40]),
            'recipe_id' => self::nullable('string', ['maxLength' => 40]),
            'quantity' => ['type' => 'number', 'minimum' => 0.25, 'maximum' => 2000],
            'unit' => ['type' => 'string', 'enum' => ['g', 'serving']],
        ]);

        return self::obj([
            'name' => self::str(120, 2),
            'notes' => self::str(1500),
            'days' => self::list(self::obj([
                'day_number' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 7],
                'label' => self::str(60),
                'meals' => self::list(self::obj([
                    'slot' => ['type' => 'string', 'enum' => self::SLOTS],
                    'name' => self::str(120),
                    'time' => self::nullable('string', ['maxLength' => 5]),
                    'items' => self::list($item, 1, 12),
                ]), 1, 8),
            ]), 1, 7),
        ]);
    }

    /** @return array<string, mixed> */
    public static function photoFood(): array
    {
        return self::obj([
            'items' => self::list(self::obj([
                'name' => self::str(120, 1),
                'estimated_grams' => ['type' => 'number', 'minimum' => 1, 'maximum' => 2000],
                'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                'kcal' => self::nullable('number', ['minimum' => 0, 'maximum' => 5000]),
                'protein_g' => self::nullable('number', ['minimum' => 0, 'maximum' => 500]),
                'carbs_g' => self::nullable('number', ['minimum' => 0, 'maximum' => 500]),
                'fat_g' => self::nullable('number', ['minimum' => 0, 'maximum' => 500]),
            ]), 0, 12),
            'notes' => self::nullable('string', ['maxLength' => 500]),
        ]);
    }

    /** @return array<string, mixed> */
    public static function checkinSummary(): array
    {
        return self::obj([
            'summary' => self::str(2000, 1),
            'wins' => self::list(self::str(400), 0, 10),
            'struggles' => self::list(self::str(400), 0, 10),
            'trends' => self::list(self::obj([
                'metric' => self::str(60),
                'label' => self::str(160),
                'value' => self::nullable('number'),
                'change' => self::nullable('number'),
                'unit' => self::str(16),
                'period' => self::str(40),
            ]), 0, 12),
            'risk_flags' => self::list(self::obj([
                'type' => self::str(40),
                'severity' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                'evidence' => self::str(400),
            ]), 0, 10),
            'suggested_actions' => self::list(self::str(400), 0, 10),
            'reply_draft' => self::str(4000, 1),
            'language' => ['type' => 'string', 'enum' => self::LANGUAGES],
        ]);
    }

    /** @return array<string, mixed> */
    public static function replyDraft(): array
    {
        return self::obj([
            'reply' => self::str(4000, 1),
            'language' => ['type' => 'string', 'enum' => self::LANGUAGES],
        ]);
    }

    /** @return array<string, mixed> */
    public static function threadSummary(): array
    {
        return self::obj([
            'summary' => self::str(2000, 1),
            'key_points' => self::list(self::str(400), 0, 12),
            'open_questions' => self::list(self::str(400), 0, 10),
            'action_items' => self::list(self::str(400), 0, 10),
            'sentiment' => ['type' => 'string', 'enum' => ['positive', 'neutral', 'negative']],
        ]);
    }

    /** @return array<string, mixed> */
    public static function churnMessages(): array
    {
        return self::obj([
            'messages' => self::list(self::obj([
                'client_key' => self::str(40),
                'message' => self::str(1500, 1),
            ]), 0, 50),
        ]);
    }

    /** @return array<string, mixed> */
    public static function formCheck(): array
    {
        return self::obj([
            'overall' => ['type' => 'string', 'enum' => ['good', 'needs_work', 'unsafe', 'unclear']],
            'checklist' => self::list(self::obj([
                'rule_key' => self::str(40),
                'status' => ['type' => 'string', 'enum' => ['pass', 'warn', 'fail', 'unclear']],
                'frame_index' => self::nullable('integer', ['minimum' => 0, 'maximum' => 20]),
                'comment' => self::str(400),
            ]), 1, 12),
            'commentary' => self::str(2000),
            'cues' => self::list(self::str(200), 0, 6),
            'draft_feedback' => self::str(3000, 1),
        ]);
    }

    /** @return array<string, mixed> */
    public static function businessAsk(): array
    {
        return self::obj([
            'tool' => ['type' => 'string', 'enum' => self::BUSINESS_TOOLS],
            'period' => ['type' => 'string', 'enum' => ['this_month', 'last_month']],
            'limit' => self::nullable('integer', ['minimum' => 1, 'maximum' => 100]),
            'reasoning' => self::str(500),
        ]);
    }

    /** @return array<string, mixed> */
    public static function content(): array
    {
        return self::obj([
            'title' => self::nullable('string', ['maxLength' => 200]),
            'body' => self::str(5000, 1),
            'hashtags' => self::list(self::str(60), 0, 30),
            'language' => ['type' => 'string', 'enum' => self::LANGUAGES],
        ]);
    }

    /** @return array<string, mixed> */
    public static function starterPlan(): array
    {
        return self::obj([
            'template_id' => self::str(40, 1),
            'reason' => self::str(800),
        ]);
    }

    /** @return array<string, mixed> */
    public static function weeklyRecap(): array
    {
        return self::obj([
            'headline' => self::str(200, 1),
            'highlights' => self::list(self::str(300), 0, 8),
            'focus_next_week' => self::str(500),
            'encouragement' => self::str(500),
        ]);
    }
}
