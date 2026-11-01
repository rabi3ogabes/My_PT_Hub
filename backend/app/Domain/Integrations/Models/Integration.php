<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends BaseModel
{
    protected $table = 'integrations';

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'scopes' => 'array',
            'settings' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $hidden = ['access_token', 'refresh_token'];
}
