<?php

namespace App\Providers\Modules;

use App\Domain\Workouts\Console\ImportExercisesCommand;
use App\Domain\Workouts\Services\ClientProgram;
use App\Domain\Workouts\Services\ExerciseLibrary;
use App\Domain\Workouts\Services\PerformanceHistory;
use App\Domain\Workouts\Services\PersonalRecordDetector;
use App\Domain\Workouts\Services\ProgramAssigner;
use App\Domain\Workouts\Services\ProgramBuilder;
use App\Domain\Workouts\Services\ExerciseImporter;
use App\Domain\Workouts\Services\ProgramSchedule;
use App\Domain\Workouts\Services\ProgressionEngine;
use App\Domain\Workouts\Services\SessionLogger;
use App\Domain\Workouts\Services\WorkoutBuilder;
use Illuminate\Support\ServiceProvider;

/** Workouts module: exercise library, builder, programs, logger and sync. */
class WorkoutsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ([ExerciseLibrary::class, WorkoutBuilder::class, ProgramBuilder::class, ProgramSchedule::class, ProgramAssigner::class,
            ProgressionEngine::class, PersonalRecordDetector::class, SessionLogger::class, PerformanceHistory::class, ClientProgram::class, ExerciseImporter::class] as $service) {
            $this->app->singletonIf($service);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ImportExercisesCommand::class]);
        }
    }
}
