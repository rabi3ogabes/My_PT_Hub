<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientGroup extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'client_groups';

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_group_members', 'client_group_id', 'user_id');
    }
}
