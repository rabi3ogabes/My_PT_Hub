<?php

namespace App\Domain\Checkins\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinComment extends BaseModel
{
    protected $table = 'checkin_comments';

    public function response(): BelongsTo
    {
        return $this->belongsTo(CheckinResponse::class, 'checkin_response_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
