<?php

namespace Tests\Feature\Workouts;

use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Services\ProgramBuilder;
use App\Domain\Workouts\Services\WorkoutBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Small global library used by the workouts feature tests. */
final class Fixtures
{
    public static function library(): array
    {
        DB::table('muscle_groups')->insertOrIgnore([
            ['id' => strtolower((string) Str::ulid()), 'key' => 'chest', 'name' => json_encode(['en' => 'Chest']), 'region' => 'upper'],
            ['id' => strtolower((string) Str::ulid()), 'key' => 'quads', 'name' => json_encode(['en' => 'Quads']), 'region' => 'lower'],
            ['id' => strtolower((string) Str::ulid()), 'key' => 'triceps', 'name' => json_encode(['en' => 'Triceps']), 'region' => 'upper'],
        ]);
        DB::table('equipment')->insertOrIgnore([
            ['id' => strtolower((string) Str::ulid()), 'key' => 'barbell', 'name' => json_encode(['en' => 'Barbell'])],
            ['id' => strtolower((string) Str::ulid()), 'key' => 'dumbbell', 'name' => json_encode(['en' => 'Dumbbells'])],
            ['id' => strtolower((string) Str::ulid()), 'key' => 'bench', 'name' => json_encode(['en' => 'Bench'])],
        ]);

        $make = fn (array $attrs) => tap(new Exercise(['tenant_id' => null] + $attrs), fn (Exercise $e) => $e->save());

        return [
            'bench' => $make([
                'slug' => 'barbell-bench-press',
                'name' => ['en' => 'Barbell Bench Press', 'ar' => 'ضغط البنش بالبار', 'fr' => 'Développé couché à la barre', 'es' => 'Press de banca con barra'],
                'aliases' => ['en' => ['bench', 'flat bench'], 'ar' => ['بنش برس'], 'fr' => ['DC'], 'es' => ['press banca']],
                'primary_muscle' => 'chest', 'secondary_muscles' => ['triceps'], 'equipment' => ['barbell', 'bench'],
                'pattern' => 'push_h', 'mechanics' => 'compound', 'force' => 'push', 'level' => 'intermediate', 'metrics' => ['weight', 'reps'],
                'instructions' => ['en' => ['Lie on the bench.', 'Lower the bar to your chest.', 'Press up.'], 'ar' => ['استلقِ على البنش.']],
            ]),
            'db_bench' => $make([
                'slug' => 'dumbbell-bench-press',
                'name' => ['en' => 'Dumbbell Bench Press', 'ar' => 'ضغط صدر بالدمبل', 'fr' => 'Développé couché aux haltères', 'es' => 'Press de banca con mancuernas'],
                'aliases' => ['en' => ['DB bench'], 'ar' => ['ضغط صدر دمبل'], 'fr' => ['DC haltères'], 'es' => ['press con mancuernas']],
                'primary_muscle' => 'chest', 'secondary_muscles' => ['triceps'], 'equipment' => ['dumbbell', 'bench'],
                'pattern' => 'push_h', 'mechanics' => 'compound', 'force' => 'push', 'level' => 'beginner', 'metrics' => ['weight', 'reps'],
            ]),
            'squat' => $make([
                'slug' => 'back-squat',
                'name' => ['en' => 'Back Squat', 'ar' => 'سكوات خلفي بالبار', 'fr' => 'Squat arrière', 'es' => 'Sentadilla trasera'],
                'aliases' => ['en' => ['squat'], 'ar' => ['قرفصاء'], 'fr' => ['squat'], 'es' => ['sentadilla']],
                'primary_muscle' => 'quads', 'equipment' => ['barbell'], 'pattern' => 'squat', 'mechanics' => 'compound', 'force' => 'push', 'level' => 'intermediate',
                'metrics' => ['weight', 'reps'],
            ]),
            'pushup' => $make([
                'slug' => 'push-up',
                'name' => ['en' => 'Push-up', 'ar' => 'تمرين الضغط', 'fr' => 'Pompes', 'es' => 'Flexiones'],
                'primary_muscle' => 'chest', 'equipment' => [], 'pattern' => 'push_h', 'level' => 'beginner', 'is_bodyweight' => true, 'metrics' => ['reps'],
            ]),
        ];
    }

    /** A tenant workout payload (nested). */
    public static function workoutPayload(array $ex, string $name = 'Push Day'): array
    {
        return [
            'name' => $name,
            'difficulty' => 'intermediate',
            'tags' => ['push'],
            'blocks' => [
                ['type' => 'warmup', 'name' => 'Warm-up', 'exercises' => [
                    ['exercise_id' => $ex['pushup']->id, 'sets' => [['set_type' => 'warmup', 'reps_min' => 10]]],
                ]],
                ['type' => 'straight', 'exercises' => [
                    ['exercise_id' => $ex['bench']->id, 'rest_seconds' => 120, 'progression_rule' => ['type' => 'linear_load', 'increment_kg' => 2.5], 'sets' => [
                        ['set_type' => 'working', 'reps_min' => 5, 'reps_max' => 5, 'weight_kg' => 60],
                        ['set_type' => 'working', 'reps_min' => 5, 'reps_max' => 5, 'weight_kg' => 60],
                        ['set_type' => 'working', 'reps_min' => 5, 'reps_max' => 5, 'weight_kg' => 60],
                    ]],
                ]],
                ['type' => 'superset', 'rounds' => 3, 'rest_between_rounds' => 90, 'exercises' => [
                    ['exercise_id' => $ex['db_bench']->id, 'sets' => [['reps_min' => 10, 'reps_max' => 12, 'weight_kg' => 20]]],
                    ['exercise_id' => $ex['pushup']->id, 'sets' => [['set_type' => 'amrap']]],
                ]],
                ['type' => 'tabata', 'rounds' => 8, 'work_seconds' => 20, 'rest_seconds' => 10, 'exercises' => [
                    ['exercise_id' => $ex['squat']->id, 'sets' => []],
                ]],
            ],
        ];
    }

    public static function workout(array $ex, ?string $createdBy = null, string $name = 'Push Day'): Workout
    {
        return app(WorkoutBuilder::class)->create(self::workoutPayload($ex, $name), $createdBy);
    }

    /** 2-week program with the workout on days 1 and 3 of each week (inside the tenant context). */
    public static function program(Workout $workout, int $weeks = 2): Program
    {
        $days = [
            ['day_number' => 1, 'workout_id' => $workout->id, 'title' => 'Push'],
            ['day_number' => 2, 'is_rest' => true],
            ['day_number' => 3, 'workout_id' => $workout->id],
        ];

        return app(ProgramBuilder::class)->create([
            'name' => ['en' => 'Strength Base', 'ar' => 'أساس القوة', 'fr' => 'Base de force', 'es' => 'Base de fuerza'],
            'level' => 'beginner',
            'days_per_week' => 2,
            'weeks' => array_map(fn ($i) => ['days' => $days, 'is_deload' => $i === $weeks], range(1, $weeks)),
        ]);
    }
}
