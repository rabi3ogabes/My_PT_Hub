<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workout extends BaseModel
{
    use SharedWithTenant, SoftDeletes;

    protected $table = 'workouts';

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'equipment_summary' => 'array',
            'is_template' => 'boolean',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(WorkoutBlock::class)->orderBy('position');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
