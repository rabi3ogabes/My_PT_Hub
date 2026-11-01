<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramDay extends BaseModel
{
    protected $table = 'program_days';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'habit_ids' => 'array',
            'is_rest' => 'boolean',
        ];
    }

    public function week(): BelongsTo
    {
        return $this->belongsTo(ProgramWeek::class, 'program_week_id');
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }
}
