<?php

namespace App\Domain\Habits\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends BaseModel
{
    protected $table = 'habit_logs';

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'value' => 'float',
            'completed' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(HabitAssignment::class, 'habit_assignment_id');
    }
}
