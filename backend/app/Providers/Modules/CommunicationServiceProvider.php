<?php

namespace App\Providers\Modules;

use App\Domain\Calendar\Services\BookingService;
use App\Domain\Messaging\Listeners\RunAutomationTriggers;
use App\Domain\Tenancy\Events\ClientJoined;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/** Phase 4: messaging, notifications, community, calendar & bookings. */
class CommunicationServiceProvider extends ServiceProvider
{
    private const PR_EVENT = 'App\\Domain\\Workouts\\Events\\PersonalRecordAchieved';

    private const WORKOUT_COMPLETED_EVENT = 'App\\Domain\\Workouts\\Events\\WorkoutCompleted';

    public function register(): void
    {
        // Per-request memo of booking settings (safe under Octane).
        $this->app->scoped(BookingService::class);
    }

    public function boot(): void
    {
        Event::listen(ClientJoined::class, [RunAutomationTriggers::class, 'clientJoined']);

        if (class_exists(self::PR_EVENT)) {
            Event::listen(self::PR_EVENT, [RunAutomationTriggers::class, 'personalRecord']);
        }
        if (class_exists(self::WORKOUT_COMPLETED_EVENT)) {
            Event::listen(self::WORKOUT_COMPLETED_EVENT, [RunAutomationTriggers::class, 'workoutCompleted']);
        }
    }
}
