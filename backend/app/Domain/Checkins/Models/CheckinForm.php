<?php

namespace App\Domain\Checkins\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckinForm extends BaseModel
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'checkin_forms';

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'include_metrics' => 'boolean',
            'include_photos' => 'boolean',
        ];
    }
}
