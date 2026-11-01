<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use App\Domain\Workouts\Support\SearchText;
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

    protected static function booted(): void
    {
        static::saving(function (Exercise $exercise): void {
            $exercise->search_text = SearchText::build($exercise->name ?? [], $exercise->aliases ?? [], (string) $exercise->slug);
        });
    }

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
            'progression_step' => 'integer',
            'video_size_bytes' => 'integer',
        ];
    }

    public function alternatives(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_alternatives', 'exercise_id', 'alternative_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(\App\Domain\Identity\Models\User::class, 'exercise_favorites', 'exercise_id', 'user_id');
    }

    public function isCustom(): bool
    {
        return $this->tenant_id !== null;
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
