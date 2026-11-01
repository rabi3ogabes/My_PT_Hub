<?php

namespace App\Domain\Checkins\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinSchedule extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'checkin_schedules';

    protected function casts(): array
    {
        return [
            'next_due_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(CheckinForm::class, 'checkin_form_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
