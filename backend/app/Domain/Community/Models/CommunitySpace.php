<?php

namespace App\Domain\Community\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunitySpace extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'community_spaces';

    protected function casts(): array
    {
        return [
            'announcement_only' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
