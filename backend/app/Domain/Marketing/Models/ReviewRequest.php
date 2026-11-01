<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class ReviewRequest extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'review_requests';

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
