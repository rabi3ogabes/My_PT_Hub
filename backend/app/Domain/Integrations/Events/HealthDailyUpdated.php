<?php

namespace App\Domain\Integrations\Events;

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

/** health_daily rows changed for the given local dates (readiness, compliance and AI listen to this). */
class HealthDailyUpdated
{
    use Dispatchable;

    /** @param list<string> $dates */
    public function __construct(public readonly User $user, public readonly array $dates) {}
}
