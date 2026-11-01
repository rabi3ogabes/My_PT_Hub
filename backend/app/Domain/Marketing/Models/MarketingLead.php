<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Shared\Models\BaseModel;

class MarketingLead extends BaseModel
{
    protected $table = 'marketing_leads';

    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
        ];
    }
}
