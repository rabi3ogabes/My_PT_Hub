<?php

use App\Domain\Checkins\Services\CheckinService;
use Illuminate\Support\Facades\Schedule;

// Check-in automation (Phase 3): due responses from schedules, push + email reminders, overdue escalation to coaches.
Schedule::call(fn () => app(CheckinService::class)->generateDue())->name('checkins:generate-due')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
Schedule::call(fn () => app(CheckinService::class)->sendReminders())->name('checkins:reminders')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
Schedule::call(fn () => app(CheckinService::class)->markOverdue())->name('checkins:overdue')->hourly()->withoutOverlapping()->onOneServer();
