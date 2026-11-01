<?php

use App\Domain\Messaging\Services\AutomationEngine;
use App\Domain\Messaging\Services\BroadcastService;
use Illuminate\Support\Facades\Schedule;

// Scheduled messages / broadcasts (each recipient gets the body in their locale, once).
Schedule::call(fn () => app(BroadcastService::class)->dispatchDue())->name('messaging:broadcasts')->everyMinute()->withoutOverlapping()->onOneServer();

// Time-based automation triggers (missed workouts, overdue check-ins, birthdays, expiring credits, program start/finish).
Schedule::call(fn () => app(AutomationEngine::class)->scan())->name('messaging:automations-scan')->hourly()->withoutOverlapping()->onOneServer();
