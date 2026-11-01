<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'workout_sessions';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'total_volume_kg' => 'float',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(SessionSet::class)->orderBy('completed_at');
    }

    public function programDay(): BelongsTo
    {
        return $this->belongsTo(ProgramDay::class);
    }
}
