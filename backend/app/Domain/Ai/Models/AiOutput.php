<?php

namespace App\Domain\Ai\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiOutput extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'ai_outputs';

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'payload' => 'array',
            'grounding' => 'array',
            'flags' => 'array',
            'edited_payload' => 'array',
            'edited_diff' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(AiRequest::class, 'ai_request_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** The payload a coach approved: their edit when present, else the draft. */
    public function effectivePayload(): array
    {
        return $this->edited_payload ?? $this->payload ?? [];
    }

    public function isPending(): bool
    {
        return $this->status === 'draft';
    }
}
