<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class Location extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'locations';
}
