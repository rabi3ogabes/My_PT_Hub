<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends BaseModel
{
    protected $table = 'message_attachments';

    protected function casts(): array
    {
        return [
            'waveform' => 'array',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
