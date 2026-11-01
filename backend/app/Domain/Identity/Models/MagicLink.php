<?php

namespace App\Domain\Identity\Models;

use App\Domain\Shared\Models\BaseModel;

class MagicLink extends BaseModel
{
    protected $table = 'magic_links';

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
