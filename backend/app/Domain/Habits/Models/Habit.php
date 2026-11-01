<?php

namespace App\Domain\Habits\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;

class Habit extends BaseModel
{
    use HasTranslations, SharedWithTenant;

    protected $table = 'habits';

    /** @var list<string> */
    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'target' => 'float',
        ];
    }
}
