<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthDaily extends BaseModel
{
    protected $table = 'health_daily';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'value' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
