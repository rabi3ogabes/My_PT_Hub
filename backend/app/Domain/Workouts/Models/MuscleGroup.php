<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;

class MuscleGroup extends BaseModel
{
    use HasTranslations;

    protected $table = 'muscle_groups';

    public $timestamps = false;

    /** @var list<string> */
    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
        ];
    }
}
