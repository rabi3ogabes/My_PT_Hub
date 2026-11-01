<?php

namespace App\Http\Api\V1\Requests\Workouts;

use App\Domain\Workouts\Support\Translatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExerciseRequest extends FormRequest
{
    public const PATTERNS = ['squat', 'hinge', 'push_h', 'push_v', 'pull_h', 'pull_v', 'lunge', 'carry', 'rotation', 'core', 'cardio', 'mobility', 'isolation', 'plyometric'];

    public const METRICS = ['weight', 'reps', 'time', 'distance', 'calories', 'rpe'];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $creating = $this->isMethod('post');

        return [
            'name' => [$creating ? 'required' : 'sometimes', Translatable::rule(120)],
            'slug' => ['sometimes', 'nullable', 'string', 'max:120', 'alpha_dash'],
            'instructions' => ['sometimes', 'nullable', Translatable::rule(500, true)],
            'tips' => ['sometimes', 'nullable', Translatable::rule(500, true)],
            'mistakes' => ['sometimes', 'nullable', Translatable::rule(500, true)],
            'aliases' => ['sometimes', 'nullable', Translatable::rule(120, true)],
            'primary_muscle' => ['sometimes', 'nullable', 'string', 'max:32', Rule::exists('muscle_groups', 'key')],
            'secondary_muscles' => ['sometimes', 'nullable', 'array', 'max:10'],
            'secondary_muscles.*' => ['string', Rule::exists('muscle_groups', 'key')],
            'equipment' => ['sometimes', 'nullable', 'array', 'max:10'],
            'equipment.*' => ['string', Rule::exists('equipment', 'key')],
            'pattern' => ['sometimes', 'nullable', Rule::in(self::PATTERNS)],
            'mechanics' => ['sometimes', 'nullable', Rule::in(['compound', 'isolation'])],
            'force' => ['sometimes', 'nullable', Rule::in(['push', 'pull', 'static'])],
            'level' => ['sometimes', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_unilateral' => ['sometimes', 'boolean'],
            'is_bodyweight' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'metrics' => ['sometimes', 'array', 'min:1', 'max:6'],
            'metrics.*' => [Rule::in(self::METRICS)],
            'alternative_ids' => ['sometimes', 'array', 'max:20'],
            'alternative_ids.*' => ['string', 'size:26'],
            'video_url' => ['sometimes', 'nullable', 'url', 'max:500'],
        ];
    }
}
