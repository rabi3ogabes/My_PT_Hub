<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'automations';

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'conditions' => 'array',
            'actions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class);
    }
}
