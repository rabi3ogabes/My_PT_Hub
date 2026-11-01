<?php

namespace App\Domain\Ai\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class AiSetting extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'ai_settings';

    protected function casts(): array
    {
        return [
            'use_emoji' => 'boolean',
            'banned_words' => 'array',
            'sample_replies' => 'array',
            'disabled_features' => 'array',
            'opt_out' => 'boolean',
        ];
    }
}
