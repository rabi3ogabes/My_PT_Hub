<?php

namespace App\Domain\Checkins\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckinResponse extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'checkin_responses';

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'context' => 'array',
            'due_on' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(CheckinForm::class, 'checkin_form_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CheckinSchedule::class, 'checkin_schedule_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CheckinComment::class)->oldest();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProgressPhoto::class);
    }
}
