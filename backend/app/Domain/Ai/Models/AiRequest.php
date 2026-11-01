<?php

namespace App\Domain\Ai\Models;

use App\Domain\Shared\Models\BaseModel;

class AiRequest extends BaseModel
{
    protected $table = 'ai_requests';

    protected function casts(): array
    {
        return [
            'tokens_in' => 'integer',
            'tokens_out' => 'integer',
            'cost_micros' => 'integer',
            'latency_ms' => 'integer',
        ];
    }
}
