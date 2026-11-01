<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthSample extends BaseModel
{
    protected $table = 'health_samples';

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Samples that are not a cross-source duplicate of another sample. */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->whereNull('duplicate_of_id');
    }

    public function durationMinutes(): float
    {
        return max(0, $this->start_at->diffInSeconds($this->end_at, true) / 60);
    }
}
