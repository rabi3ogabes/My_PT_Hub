<?php

namespace App\Domain\Workouts\Events;

use App\Domain\Workouts\Models\PersonalRecord;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** A client beat a previous best (e1rm|weight|reps|volume) on an exercise. */
class PersonalRecordAchieved implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PersonalRecord $record) {}
}
