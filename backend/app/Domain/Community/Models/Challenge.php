<?php

namespace App\Domain\Community\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'challenges';

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'goal' => 'float',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ChallengeEntry::class);
    }
}
