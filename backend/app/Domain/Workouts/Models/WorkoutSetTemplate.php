<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSetTemplate extends BaseModel
{
    protected $table = 'workout_set_templates';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'weight_kg' => 'float',
            'percent_1rm' => 'float',
            'rpe' => 'float',
        ];
    }

    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }
}
