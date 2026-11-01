<?php

namespace App\Http\Api\V1\Requests\Workouts;

use App\Domain\Workouts\Services\ProgressionEngine;
use App\Domain\Workouts\Services\WorkoutBuilder;
use Illuminate\Validation\Rule;

/** Nested workout payload rules (shared by store/update/import). */
final class WorkoutRequest
{
    /** @return array<string, mixed> */
    public static function rules(bool $creating): array
    {
        return [
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:160'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'difficulty' => ['sometimes', Rule::in(WorkoutBuilder::DIFFICULTIES)],
            'tags' => ['sometimes', 'nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'is_template' => ['sometimes', 'boolean'],
            'blocks' => [$creating ? 'present' : 'sometimes', 'array', 'max:40'],
            'blocks.*.id' => ['sometimes', 'nullable', 'string', 'size:26'],
            'blocks.*.position' => ['sometimes', 'nullable', 'integer'],
            'blocks.*.type' => ['required', Rule::in(WorkoutBuilder::BLOCK_TYPES)],
            'blocks.*.name' => ['nullable', 'string', 'max:120'],
            'blocks.*.rounds' => ['nullable', 'integer', 'min:1', 'max:100'],
            'blocks.*.work_seconds' => ['nullable', 'integer', 'min:0', 'max:36000'],
            'blocks.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'blocks.*.rest_between_rounds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'blocks.*.time_cap_seconds' => ['nullable', 'integer', 'min:0', 'max:36000'],
            'blocks.*.notes' => ['nullable', 'string', 'max:2000'],
            'blocks.*.exercises' => ['present', 'array', 'max:30'],
            'blocks.*.exercises.*.id' => ['sometimes', 'nullable', 'string', 'size:26'],
            'blocks.*.exercises.*.position' => ['sometimes', 'nullable', 'integer'],
            'blocks.*.exercises.*.exercise_id' => ['required', 'string', 'size:26'],
            'blocks.*.exercises.*.exercise_slug' => ['sometimes', 'nullable', 'string', 'max:160'],
            'blocks.*.exercises.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'blocks.*.exercises.*.tempo' => ['nullable', 'string', 'max:16'],
            'blocks.*.exercises.*.notes' => ['nullable', 'string', 'max:2000'],
            'blocks.*.exercises.*.alternative_ids' => ['nullable', 'array', 'max:10'],
            'blocks.*.exercises.*.alternative_ids.*' => ['string', 'size:26'],
            'blocks.*.exercises.*.progression_rule' => ['nullable', 'array'],
            'blocks.*.exercises.*.progression_rule.type' => ['required_with:blocks.*.exercises.*.progression_rule', Rule::in(ProgressionEngine::TYPES)],
            'blocks.*.exercises.*.progression_rule.increment_kg' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'blocks.*.exercises.*.progression_rule.base_percent' => ['nullable', 'numeric', 'min:10', 'max:110'],
            'blocks.*.exercises.*.progression_rule.step_percent' => ['nullable', 'numeric', 'min:-20', 'max:20'],
            'blocks.*.exercises.*.progression_rule.max_percent' => ['nullable', 'numeric', 'min:10', 'max:110'],
            'blocks.*.exercises.*.progression_rule.reps_min' => ['nullable', 'integer', 'min:1', 'max:100'],
            'blocks.*.exercises.*.progression_rule.reps_max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'blocks.*.exercises.*.progression_rule.deload_percent' => ['nullable', 'numeric', 'min:0', 'max:90'],
            'blocks.*.exercises.*.progression_rule.round_to' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'blocks.*.exercises.*.sets' => ['present', 'array', 'max:30'],
            'blocks.*.exercises.*.sets.*.id' => ['sometimes', 'nullable', 'string', 'size:26'],
            'blocks.*.exercises.*.sets.*.position' => ['sometimes', 'nullable', 'integer'],
            'blocks.*.exercises.*.sets.*.set_type' => ['nullable', Rule::in(WorkoutBuilder::SET_TYPES)],
            'blocks.*.exercises.*.sets.*.reps_min' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'blocks.*.exercises.*.sets.*.reps_max' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'blocks.*.exercises.*.sets.*.weight_kg' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'blocks.*.exercises.*.sets.*.percent_1rm' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'blocks.*.exercises.*.sets.*.rpe' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'blocks.*.exercises.*.sets.*.rir' => ['nullable', 'integer', 'min:0', 'max:10'],
            'blocks.*.exercises.*.sets.*.duration_seconds' => ['nullable', 'integer', 'min:0', 'max:36000'],
            'blocks.*.exercises.*.sets.*.distance_m' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'blocks.*.exercises.*.sets.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
        ];
    }
}
