<?php

use App\Domain\Progress\Services\ComplianceService;
use Illuminate\Support\Facades\Schedule;

// Compliance (Phase 3): hourly pass refreshes scores and sends the coach digest to tenants where it is 07:00 local time.
Schedule::call(fn () => app(ComplianceService::class)->sendDigests(7))->name('compliance:daily-digest')->hourlyAt(5)->withoutOverlapping()->onOneServer();
