<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionSet extends BaseModel
{
    protected $table = 'session_sets';

    protected function casts(): array
    {
        return [
            'weight_kg' => 'float',
            'rpe' => 'float',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
