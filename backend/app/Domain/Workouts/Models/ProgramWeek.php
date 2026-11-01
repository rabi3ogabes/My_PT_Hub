<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramWeek extends BaseModel
{
    protected $table = 'program_weeks';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_deload' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ProgramDay::class)->orderBy('day_number');
    }
}
