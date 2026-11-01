<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutSession extends BaseModel
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'workout_sessions';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'total_volume_kg' => 'float',
            'duration_seconds' => 'integer',
            'perceived_effort' => 'integer',
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
        return $this->hasMany(SessionSet::class)->orderBy('set_number')->orderBy('completed_at')->orderBy('id');
    }

    public function personalRecords(): HasMany
    {
        return $this->hasMany(PersonalRecord::class);
    }

    public function programAssignment(): BelongsTo
    {
        return $this->belongsTo(ProgramAssignment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function programDay(): BelongsTo
    {
        return $this->belongsTo(ProgramDay::class);
    }
}
