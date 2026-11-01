<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledMessage extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'scheduled_messages';

    protected function casts(): array
    {
        return [
            'segment' => 'array',
            'body' => 'array',
            'send_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
