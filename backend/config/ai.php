<?php

/*
|--------------------------------------------------------------------------
| AI Coach Copilot (Phase 6)
|--------------------------------------------------------------------------
| Driver, keys and default models live in config/services.php ('ai').
| This file holds the copilot's own knobs: per-feature model overrides,
| prompt versions, token pricing (for metering), retries and limits.
*/

return [

    // Anthropic Messages API.
    'anthropic_url' => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1/messages'),
    'anthropic_version' => env('ANTHROPIC_VERSION', '2026-10-21'),
    // output_config.effort for Claude (low|medium|high|xhigh|max); null = model default.
    'anthropic_effort' => env('ANTHROPIC_EFFORT', 'medium'),

    // OpenAI (chat completions + audio + embeddings).
    'openai_url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
    'openai_transcribe_model' => env('OPENAI_TRANSCRIBE_MODEL', 'whisper-1'),
    'openai_embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 120),

    // Structured outputs: retries after the first attempt, each feeding the
    // validation errors back to the model.
    'max_retries' => 2,

    // Optional per-feature model override (admin configurable through env).
    'feature_models' => [
        'program' => env('AI_MODEL_PROGRAM'),
        'meal_plan' => env('AI_MODEL_MEAL_PLAN'),
        'photo_food' => env('AI_MODEL_PHOTO_FOOD'),
        'form_check' => env('AI_MODEL_FORM_CHECK'),
        'business_ask' => env('AI_MODEL_BUSINESS_ASK'),
    ],

    // Max output tokens per feature.
    'max_tokens' => [
        'default' => 4096,
        'program' => 16000,
        'meal_plan' => 16000,
        'progression' => 6000,
        'checkin_summary' => 4000,
        'churn' => 6000,
    ],

    // Prompt template version per feature (resources/prompts/{feature}/{version}.md).
    // A tenant enrolled in the feature flag "ai_prompt_{feature}_v2" gets v2 when it exists.
    'prompt_versions' => [],

    // USD price per 1M tokens => cost micros per token equals the price.
    'pricing' => [
        'claude-opus-5-5' => ['in' => 4.0, 'out' => 20.0],
        'claude-sonnet-5-5' => ['in' => 2.0, 'out' => 10.0],
        'claude-haiku-5-5' => ['in' => 0.10, 'out' => 0.50],
        'gpt-4.1-mini' => ['in' => 0.40, 'out' => 1.60],
        'gpt-4.1' => ['in' => 2.0, 'out' => 8.0],
        'fake' => ['in' => 0.0, 'out' => 0.0],
        'default' => ['in' => 2.0, 'out' => 10.0],
    ],

    'form_check' => [
        'max_video_seconds' => 30,
        'max_frames' => 6,
        'ffmpeg' => env('FFMPEG_BIN', 'ffmpeg'),
        'ffprobe' => env('FFPROBE_BIN', 'ffprobe'),
    ],

    'meal_plan' => [
        'tolerance' => 0.05,
    ],

    // Daily churn list: how many at-risk clients get an AI-drafted message.
    'churn' => [
        'suggest_limit' => 10,
    ],
];
