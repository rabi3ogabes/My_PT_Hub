<?php

namespace App\Domain\Progress\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class ProgressPhoto extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'progress_photos';

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'is_private' => 'boolean',
        ];
    }
}
