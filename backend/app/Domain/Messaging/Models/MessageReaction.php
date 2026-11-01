<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReaction extends BaseModel
{
    protected $table = 'message_reactions';

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
