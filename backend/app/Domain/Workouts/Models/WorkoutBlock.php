<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutBlock extends BaseModel
{
    protected $table = 'workout_blocks';

    public $timestamps = false;

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class)->orderBy('position');
    }
}
