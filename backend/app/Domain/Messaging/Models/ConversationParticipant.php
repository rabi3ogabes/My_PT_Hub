<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends BaseModel
{
    protected $table = 'conversation_participants';

    protected function casts(): array
    {
        return [
            'is_muted' => 'boolean',
            'last_read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
