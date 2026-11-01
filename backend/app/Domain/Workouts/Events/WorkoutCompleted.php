<?php

namespace App\Domain\Workouts\Events;

use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\WorkoutSession;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A client finished a workout session. Fired once per session (first completion).
 * Listeners: compliance, notifications to the coach, streak badges, AI recap…
 */
class WorkoutCompleted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /**
     * @param  list<PersonalRecord>  $records  true PRs (beat a previous best) set in this session
     */
    public function __construct(
        public readonly WorkoutSession $session,
        public readonly array $records = [],
    ) {}
}
