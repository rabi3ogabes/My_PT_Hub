<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use App\Domain\Tenancy\Models\CoachProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends BaseModel
{
    use BelongsToTenant;

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $table = 'reviews';

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified' => 'boolean',
            'replied_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function coachProfile(): BelongsTo
    {
        return $this->belongsTo(CoachProfile::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** Reviews that may be shown publicly and count toward the aggregate rating. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'approved')->where('is_verified', true);
    }
}
