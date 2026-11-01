<?php

namespace App\Domain\Crm\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStage extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'lead_stages';

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
