<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends BaseModel
{
    use SharedWithTenant, SoftDeletes, HasTranslations;

    protected $table = 'exercises';

    /** @var list<string> */
    protected array $translatable = ['name', 'instructions', 'tips', 'mistakes'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'instructions' => 'array',
            'tips' => 'array',
            'mistakes' => 'array',
            'aliases' => 'array',
            'secondary_muscles' => 'array',
            'equipment' => 'array',
            'metrics' => 'array',
            'is_unilateral' => 'boolean',
            'is_bodyweight' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function alternatives(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_alternatives', 'exercise_id', 'alternative_id');
    }

    public function progressionParent(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'progression_parent_id');
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(Exercise::class, 'progression_parent_id');
    }
}
