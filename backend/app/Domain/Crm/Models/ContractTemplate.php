<?php

namespace App\Domain\Crm\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class ContractTemplate extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'contract_templates';
}
