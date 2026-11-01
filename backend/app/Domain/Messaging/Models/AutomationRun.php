<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends BaseModel
{
    protected $table = 'automation_runs';

    protected function casts(): array
    {
        return [
            'result' => 'array',
        ];
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}
