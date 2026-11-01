<?php

use App\Domain\Community\Services\BadgeService;
use App\Domain\Community\Services\ChallengeScorer;
use Illuminate\Support\Facades\Schedule;

// Challenge leaderboards (steps, workouts, habit streaks, volume) and completion badges.
Schedule::call(fn () => app(ChallengeScorer::class)->scoreActive())->name('community:challenges-score')->hourly()->withoutOverlapping()->onOneServer();

// Achievement badges (first workout, streaks, PR count, check-in streak).
Schedule::call(fn () => app(BadgeService::class)->sweep())->name('community:badges-sweep')->dailyAt('03:17')->withoutOverlapping()->onOneServer();
