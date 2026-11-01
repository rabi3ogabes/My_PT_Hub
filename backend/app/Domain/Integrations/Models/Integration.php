<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A user's connection to an external provider. Tokens are encrypted at rest.
 * Not tenant-scoped: a client's wearables follow the user across coaches.
 */
class Integration extends BaseModel
{
    protected $table = 'integrations';

    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'scopes' => 'array',
            'settings' => 'array',
            'last_sync_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'failure_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventLinks(): HasMany
    {
        return $this->hasMany(CalendarEventLink::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function tokenExpiresSoon(int $seconds = 300): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->lte(now()->addSeconds($seconds));
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    /** @param array<string, mixed> $values */
    public function mergeSettings(array $values): static
    {
        $this->settings = array_replace($this->settings ?? [], $values);

        return $this;
    }
}
