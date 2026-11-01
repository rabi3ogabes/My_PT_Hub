<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutExercise extends BaseModel
{
    protected $table = 'workout_exercises';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'alternative_ids' => 'array',
            'progression_rule' => 'array',
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(WorkoutBlock::class, 'workout_block_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSetTemplate::class)->orderBy('position');
    }
}
