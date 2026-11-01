<?php

use App\Domain\Payments\Jobs\SnapshotAnalytics;
use App\Domain\Payments\Services\FulfilmentService;
use App\Domain\Payments\Services\SubscriptionService;
use Illuminate\Support\Facades\Schedule;

// Client → coach payments: renewals & dunning, period-end cancellations, access expiry, analytics snapshots.
Schedule::call(fn () => app(SubscriptionService::class)->expireEnded())->name('payments:expire-ended')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
Schedule::call(fn () => app(SubscriptionService::class)->processRenewals())->name('payments:renewals')->hourly()->withoutOverlapping()->onOneServer();
Schedule::call(fn () => app(FulfilmentService::class)->expireAccess())->name('payments:expire-access')->hourlyAt(20)->withoutOverlapping()->onOneServer();
Schedule::job(new SnapshotAnalytics)->name('payments:analytics-snapshot')->dailyAt('01:10')->withoutOverlapping()->onOneServer();
