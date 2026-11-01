<?php

namespace App\Providers\Modules;

use App\Domain\Habits\Services\StreakService;
use App\Domain\Nutrition\Services\MacroCalculator;
use App\Domain\Nutrition\Services\NutritionCalculator;
use App\Domain\Nutrition\Services\SolarCalculator;
use App\Domain\Progress\Services\ComplianceService;
use Illuminate\Support\ServiceProvider;

/** Phase 3 module: nutrition, habits, check-ins, progress & compliance. */
class NutritionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MacroCalculator::class);
        $this->app->singleton(NutritionCalculator::class);
        $this->app->singleton(SolarCalculator::class);
        $this->app->singleton(StreakService::class);
        $this->app->bind(ComplianceService::class);
    }

    public function boot(): void
    {
        // Progress photos and check-in files live on a private disk (never publicly served).
        if (config('filesystems.disks.private') === null) {
            config()->set('filesystems.disks.private', [
                'driver' => 'local',
                'root' => storage_path('app/private'),
                'serve' => false,
                'throw' => false,
                'report' => false,
                'visibility' => 'private',
            ]);
        }
    }
}
