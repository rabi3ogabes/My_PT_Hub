<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use App\Domain\Tenancy\Models\CoachProfile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'reviews';

    public function coachProfile(): BelongsTo
    {
        return $this->belongsTo(CoachProfile::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
