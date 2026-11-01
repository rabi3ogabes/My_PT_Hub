<?php

namespace App\Domain\Habits\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HabitAssignment extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'habit_assignments';

    protected function casts(): array
    {
        return [
            'days' => 'array',
            'reminder_times' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'target' => 'float',
        ];
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }
}
