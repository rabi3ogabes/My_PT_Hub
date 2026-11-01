<?php

namespace Database\Seeders\Modules;

use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Services\ProgramBuilder;
use App\Domain\Workouts\Services\WorkoutBuilder;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

/**
 * Platform-public starter programs (tenant_id null, visibility public, is_template)
 * with real workouts, blocks and set targets. Runs after ExerciseSeeder; idempotent
 * (programs keyed by slug, template workouts by name).
 */
class ProgramTemplateSeeder extends Seeder
{
    private const RULES = [
        'linear' => ['type' => 'linear_load', 'increment_kg' => 2.5],
        'linear5' => ['type' => 'linear_load', 'increment_kg' => 5],
        'pct' => ['type' => 'percent_week', 'base_percent' => 65, 'step_percent' => 5, 'max_percent' => 90, 'deload_percent' => 30],
    ];

    public function run(WorkoutBuilder $workoutBuilder, ProgramBuilder $programBuilder): void
    {
        if (! Exercise::withoutGlobalScopes()->whereNull('tenant_id')->exists()) {
            $this->call(ExerciseSeeder::class);
        }
        $data = (static fn () => require database_path('data/program_templates.php'))();
        $slugToId = Exercise::withoutGlobalScopes()->whereNull('tenant_id')->pluck('id', 'slug');

        $workoutIds = [];
        foreach ($data['workouts'] as $key => [$name, $difficulty, $tags, $blocks]) {
            $payload = [
                'name' => $name,
                'difficulty' => $difficulty,
                'tags' => $tags,
                'is_template' => true,
                'blocks' => array_map(fn (array $b) => $this->block($b, $slugToId->all()), $blocks),
            ];
            $existing = Workout::withoutGlobalScopes()->whereNull('tenant_id')->whereNull('deleted_at')
                ->where('is_template', true)->where('name', $name)->first();
            $workout = $existing ? $workoutBuilder->replace($existing, $payload) : $workoutBuilder->create($payload, null, null, true);
            $workoutIds[$key] = $workout->id;
        }

        foreach ($data['programs'] as $def) {
            [$slug, $names, $descriptions, $goal, $level, $daysPerWeek, $equipment, $tags, $weeks, $deloads, $phase, $days] = $def;
            $daysB = $def[12] ?? null;
            $payload = [
                'slug' => $slug,
                'name' => array_combine(['en', 'ar', 'fr', 'es'], $names),
                'description' => array_combine(['en', 'ar', 'fr', 'es'], $descriptions),
                'goal' => $goal,
                'level' => $level,
                'days_per_week' => $daysPerWeek,
                'equipment' => $equipment,
                'tags' => $tags,
                'visibility' => 'public',
                'is_template' => true,
                'weeks' => array_map(function (int $n) use ($deloads, $phase, $days, $daysB, $workoutIds) {
                    $map = ($daysB && $n % 2 === 0) ? $daysB : $days;
                    $deload = in_array($n, $deloads, true);

                    return [
                        'phase' => $deload ? 'deload' : $phase,
                        'is_deload' => $deload,
                        'days' => collect(range(1, 7))->map(fn (int $d) => isset($map[$d])
                            ? ['day_number' => $d, 'workout_id' => $workoutIds[$map[$d]]]
                            : ['day_number' => $d, 'is_rest' => true])->all(),
                    ];
                }, range(1, $weeks)),
            ];

            $existing = Program::withoutGlobalScopes()->whereNull('tenant_id')->whereNull('deleted_at')->where('slug', $slug)->first();
            $existing ? $programBuilder->replace($existing, $payload) : $programBuilder->create($payload, null, true);
        }

        $this->command?->info(count($data['programs']).' program templates and '.count($workoutIds).' template workouts seeded.');
    }

    /**
     * @param  array<string, mixed>  $b
     * @param  array<string, string>  $slugToId
     * @return array<string, mixed>
     */
    private function block(array $b, array $slugToId): array
    {
        $rows = $b['ex'];
        unset($b['ex']);

        $b['exercises'] = array_map(function (array $row) use ($slugToId) {
            [$slug, $spec] = $row;
            $id = $slugToId[$slug] ?? throw new InvalidArgumentException("Unknown exercise slug [{$slug}] in program templates.");
            $sets = $this->sets((string) $spec);
            $rule = null;
            if (isset($row[3])) {
                if ($row[3] === 'reps') {
                    $working = collect($sets)->firstWhere('set_type', 'working') ?? [];
                    $rule = ['type' => 'rep_progression', 'reps_min' => $working['reps_min'] ?? 8, 'reps_max' => $working['reps_max'] ?? 12, 'increment_kg' => 2.5];
                } else {
                    $rule = self::RULES[$row[3]] ?? throw new InvalidArgumentException("Unknown progression [{$row[3]}].");
                }
            }

            return [
                'exercise_id' => $id,
                'rest_seconds' => $row[2] ?? null,
                'progression_rule' => $rule,
                'sets' => $sets,
            ];
        }, $rows);

        return $b;
    }

    /**
     * "w1x8,4x6-8@75%" → set templates.
     *
     * @return list<array<string, mixed>>
     */
    public function sets(string $spec): array
    {
        $sets = [];
        foreach (array_filter(array_map('trim', explode(',', $spec))) as $segment) {
            if (! preg_match('/^(?<t>[wadf])?(?<n>\d+)x(?<v>[^@]+)(?:@(?<i>[\d.]+)(?<pct>%)?)?$/', $segment, $m)) {
                throw new InvalidArgumentException("Bad set spec [{$segment}].");
            }
            $set = ['set_type' => match ($m['t'] ?? '') {
                'w' => 'warmup', 'a' => 'amrap', 'd' => 'drop', 'f' => 'failure', default => 'working',
            }];
            $value = $m['v'];
            if (preg_match('/^(\d+)-(\d+)$/', $value, $r)) {
                $set += ['reps_min' => (int) $r[1], 'reps_max' => (int) $r[2]];
            } elseif (preg_match('/^(\d+)s$/', $value, $r)) {
                $set['duration_seconds'] = (int) $r[1];
            } elseif (preg_match('/^(\d+)m$/', $value, $r)) {
                $set['distance_m'] = (int) $r[1];
            } elseif (ctype_digit($value)) {
                $set += ['reps_min' => (int) $value, 'reps_max' => (int) $value];
            } else {
                throw new InvalidArgumentException("Bad set value [{$value}].");
            }
            if (($m['i'] ?? '') !== '') {
                ! empty($m['pct']) ? $set['percent_1rm'] = (float) $m['i'] : $set['rpe'] = (float) $m['i'];
            }
            for ($i = 0; $i < (int) $m['n']; $i++) {
                $sets[] = $set;
            }
        }

        return $sets;
    }
}
