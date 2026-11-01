<?php

use App\Domain\Billing\Services\SaasBillingService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn () => app(SaasBillingService::class)->expireEnded())->name('billing:expire-ended')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
Schedule::call(fn () => app(SaasBillingService::class)->sendReminders())->name('billing:reminders')->hourly()->withoutOverlapping()->onOneServer();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();
