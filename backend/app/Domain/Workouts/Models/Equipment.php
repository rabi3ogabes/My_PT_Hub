<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;

class Equipment extends BaseModel
{
    use HasTranslations;

    protected $table = 'equipment';

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
