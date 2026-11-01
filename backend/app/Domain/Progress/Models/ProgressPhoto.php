<?php

namespace App\Domain\Progress\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
