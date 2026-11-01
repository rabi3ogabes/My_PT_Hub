<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

// Heartbeat read by the admin System Health page to prove the scheduler runs.
Schedule::call(fn () => Cache::forever('admin:scheduler:last_run', now()->toIso8601String()))
    ->name('admin:scheduler-heartbeat')
    ->everyMinute()
    ->onOneServer();
