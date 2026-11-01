<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Shared\Models\BaseModel;

class FeatureFlag extends BaseModel
{
    protected $table = 'feature_flags';

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'plans' => 'array',
            'tenants' => 'array',
            'rollout' => 'integer',
        ];
    }
}
