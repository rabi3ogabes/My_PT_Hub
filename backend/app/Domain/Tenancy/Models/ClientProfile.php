<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientProfile extends BaseModel
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'client_profiles';

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'encrypted',
            'injuries' => 'encrypted',
            'medical_notes' => 'encrypted',
            'equipment' => 'array',
            'diet_tags' => 'array',
            'tags' => 'array',
            'onboarding' => 'array',
            'parq_flagged' => 'boolean',
            'last_active_at' => 'datetime',
            'height_cm' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function age(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }
        try {
            return \Carbon\CarbonImmutable::parse($this->date_of_birth)->age;
        } catch (\Throwable) {
            return null;
        }
    }
}
