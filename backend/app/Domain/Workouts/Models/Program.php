<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends BaseModel
{
    use SharedWithTenant, SoftDeletes, HasTranslations;

    protected $table = 'programs';

    /** @var list<string> */
    protected array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'equipment' => 'array',
            'tags' => 'array',
            'is_template' => 'boolean',
        ];
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(ProgramWeek::class)->orderBy('number');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProgramAssignment::class);
    }
}
