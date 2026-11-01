<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAssignment extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'program_assignments';

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'paused_at' => 'date',
            'shift_days' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
