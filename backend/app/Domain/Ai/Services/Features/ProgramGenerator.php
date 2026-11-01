<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\ExerciseRetriever;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Ai\Support\RequestParser;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Ai\Support\SchemaValidator;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\ProgramWeek;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSetTemplate;

/**
 * A. Program generator: client profile and/or free text -> full program draft
 * built ONLY from library exercises. Unknown exercise ids/names returned by
 * the model are mapped to the closest library exercise server-side; anything
 * unmappable is fed back for a retry. Applying the approved draft creates a
 * real Program with weeks, days and workouts in the tenant.
 */
final class ProgramGenerator
{
    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly ExerciseRetriever $exercises,
        private readonly SafetyGuard $guard,
        private readonly SchemaValidator $validator,
    ) {}

    /** @param array<string, mixed> $input */
    public function generate(Tenant $tenant, User $coach, array $input): AiOutput
    {
        $client = ! empty($input['client_id']) ? $this->clients->resolveClient($tenant, $input['client_id']) : null;
        $profile = $client ? $this->clients->promptProfile($tenant, $client) : null;
        $parsed = RequestParser::program($input['prompt'] ?? null);

        $request = [
            'goal' => $input['goal'] ?? $parsed['goal'] ?? $profile['goal'] ?? 'general',
            'level' => $input['level'] ?? $parsed['level'] ?? $profile['level'] ?? 'intermediate',
            'days_per_week' => (int) ($input['days_per_week'] ?? $parsed['days_per_week'] ?? $profile['days_per_week'] ?? 3),
            'session_minutes' => (int) ($input['session_minutes'] ?? $parsed['session_minutes'] ?? 60),
            'weeks' => (int) ($input['weeks'] ?? $parsed['weeks'] ?? 4),
            'equipment' => array_values($input['equipment'] ?? ($parsed['equipment'] ?: ($profile['equipment'] ?? []))),
            'focus' => array_values($input['focus'] ?? []),
            'limitations' => $input['injuries'] ?? $profile['injuries'] ?? null,
            'notes' => $input['prompt'] ?? null,
        ];
        if (! in_array($request['goal'], Schemas::GOALS, true)) {
            $request['goal'] = 'general';
        }
        $language = $this->clients->language($client, $input['language'] ?? null, $tenant);

        $safety = $this->guard->screenText(trim(($input['prompt'] ?? '').' '.($request['limitations'] ?? '')));
        if ($client) {
            $safety = $safety->merge($this->guard->screenProfile($this->clients->profile($tenant, $client)));
        }

        $candidates = $this->exercises->candidates([
            'equipment' => $request['equipment'],
            'muscles' => $request['focus'],
            'level' => $request['level'],
            'query' => $request['notes'],
        ], 60);
        if ($candidates === []) {
            throw ApiException::invalid('exercise_library_empty', __('ai.errors.library_empty'));
        }
        $mapped = [];

        $result = $this->gateway->run(new AiTask(
            feature: 'program',
            tenant: $tenant,
            user: $coach,
            kind: 'structured',
            variables: [
                'request' => $request,
                'client' => $profile ?? '-',
                'exercises' => $candidates,
                'language' => $language,
                'safety_flags' => $safety->categories,
            ],
            context: ['request' => $request, 'candidates' => $candidates, 'language' => $language],
            schema: Schemas::program(),
            postProcess: function (array $data) use ($request, &$mapped) {
                $mapped = [];

                return $this->normalize($data, $request['equipment'], $mapped);
            },
        ));

        $payload = $result->data + [
            'mapped_exercises' => $mapped,
            'request' => $request,
            'language' => $language,
        ];

        return $this->outputs->draft(
            'program',
            $coach,
            $client,
            array_filter(['prompt' => $input['prompt'] ?? null, 'client_id' => $client?->id] + $request, fn ($v) => $v !== null),
            $payload,
            $this->clients->grounding($tenant, $client, ['exercise_candidates' => count($candidates)]),
            $safety->toFlags(),
            $result->requestId,
        );
    }

    /**
     * Server-side checks and repairs of a program payload.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $equipment
     * @param  list<array<string, string>>  $mapped
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public function normalize(array $data, array $equipment, array &$mapped, bool $allowMapping = true): array
    {
        $errors = [];
        $seenDays = [];

        foreach ($data['days'] as $d => &$day) {
            if (in_array($day['day_number'], $seenDays, true)) {
                $errors[] = "days[{$d}].day_number {$day['day_number']} is duplicated";
            }
            $seenDays[] = $day['day_number'];

            foreach ($day['workout']['blocks'] as $b => &$block) {
                foreach ($block['exercises'] as $e => &$exercise) {
                    $path = "days[{$d}].workout.blocks[{$b}].exercises[{$e}]";
                    $found = $this->exercises->find((string) $exercise['exercise_id']);
                    if ($found === null && $allowMapping) {
                        $found = $this->exercises->closest((string) $exercise['exercise_name'], $equipment);
                        if ($found !== null) {
                            $mapped[] = ['from' => (string) ($exercise['exercise_name'] ?: $exercise['exercise_id']), 'to_id' => $found->id, 'to_name' => (string) $found->translate('name', 'en')];
                        }
                    }
                    if ($found === null) {
                        $errors[] = "{$path}.exercise_id '{$exercise['exercise_id']}' is not in the exercise library; pick an id from the provided list";

                        continue;
                    }
                    $exercise['exercise_id'] = $found->id;
                    $exercise['exercise_name'] = (string) $found->translate('name', 'en');

                    foreach ($exercise['sets'] as &$set) {
                        if ($set['reps_min'] !== null && $set['reps_max'] !== null && $set['reps_min'] > $set['reps_max']) {
                            [$set['reps_min'], $set['reps_max']] = [$set['reps_max'], $set['reps_min']];
                        }
                    }
                    unset($set);
                }
                unset($exercise);
            }
            unset($block);
        }
        unset($day);

        usort($data['days'], fn ($a, $b) => $a['day_number'] <=> $b['day_number']);
        $data['days_per_week'] = count($data['days']);
        if ($data['deload_week'] !== null && $data['deload_week'] > $data['weeks_count']) {
            $data['deload_week'] = null;
        }

        return [$data, $errors];
    }

    /**
     * @param  array<string, mixed>  $edited
     * @return list<string>
     */
    public function validateEdited(AiOutput $output, array $edited): array
    {
        $errors = $this->validator->validate($this->strip($edited), Schemas::program());
        if ($errors !== []) {
            return $errors;
        }
        $mapped = [];
        [, $errors] = $this->normalize($this->strip($edited), [], $mapped, allowMapping: false);

        return $errors;
    }

    public function apply(AiOutput $output, User $reviewer): Program
    {
        $data = $output->effectivePayload();
        $mapped = [];
        [$data, $errors] = $this->normalize($this->strip($data), [], $mapped, allowMapping: false);
        if ($errors !== []) {
            throw ApiException::invalid('validation_failed', __('ai.errors.edit_invalid'), ['payload' => array_slice($errors, 0, 10)]);
        }
        $locale = $output->payload['language'] ?? app()->getLocale();
        $equipment = $output->payload['request']['equipment'] ?? [];

        $program = Program::query()->create([
            'created_by' => $reviewer->id,
            'name' => [$locale => $data['name']],
            'description' => [$locale => $data['description']],
            'goal' => $data['goal'],
            'level' => $data['level'],
            'days_per_week' => $data['days_per_week'],
            'equipment' => $equipment,
            'tags' => ['ai_generated'],
            'visibility' => 'private',
            'is_template' => $output->client_id === null,
        ]);

        $workoutIds = [];
        foreach ($data['days'] as $day) {
            $workoutIds[$day['day_number']] = $this->createWorkout($day, $data, $reviewer)->id;
        }

        $phase = match ($data['goal']) {
            'strength' => 'strength',
            'muscle_gain' => 'hypertrophy',
            default => null,
        };
        for ($w = 1; $w <= $data['weeks_count']; $w++) {
            $deload = $data['deload_week'] === $w;
            $week = ProgramWeek::query()->create([
                'program_id' => $program->id,
                'number' => $w,
                'phase' => $deload ? 'deload' : $phase,
                'is_deload' => $deload,
                'notes' => $deload ? __('ai.program.deload_note', [], $locale) : null,
            ]);
            for ($dayNumber = 1; $dayNumber <= 7; $dayNumber++) {
                $workoutId = $workoutIds[$dayNumber] ?? null;
                $title = collect($data['days'])->firstWhere('day_number', $dayNumber)['title'] ?? null;
                ProgramDay::query()->create([
                    'program_week_id' => $week->id,
                    'day_number' => $dayNumber,
                    'workout_id' => $workoutId,
                    'title' => $workoutId ? $title : __('ai.program.rest_day', [], $locale),
                    'is_rest' => $workoutId === null,
                ]);
            }
        }

        return $program;
    }

    /** @param array<string, mixed> $day */
    private function createWorkout(array $day, array $program, User $reviewer): Workout
    {
        $equipment = [];
        $workout = Workout::query()->create([
            'created_by' => $reviewer->id,
            'name' => $day['workout']['name'],
            'description' => $day['title'],
            'tags' => ['ai_generated'],
            'difficulty' => $program['level'],
            'estimated_minutes' => $day['workout']['estimated_minutes'],
            'is_template' => false,
        ]);

        foreach (array_values($day['workout']['blocks']) as $bi => $block) {
            $blockModel = WorkoutBlock::query()->create([
                'workout_id' => $workout->id,
                'position' => $bi + 1,
                'type' => $block['type'],
                'name' => $block['name'],
                'rounds' => $block['rounds'],
                'rest_seconds' => $block['rest_seconds'],
            ]);
            foreach (array_values($block['exercises']) as $ei => $exercise) {
                $library = $this->exercises->find($exercise['exercise_id']);
                array_push($equipment, ...($library?->equipment ?? []));
                $we = WorkoutExercise::query()->create([
                    'workout_block_id' => $blockModel->id,
                    'exercise_id' => $exercise['exercise_id'],
                    'position' => $ei + 1,
                    'rest_seconds' => $exercise['rest_seconds'],
                    'tempo' => $exercise['tempo'],
                    'notes' => $exercise['notes'],
                    'progression_rule' => array_filter([
                        'type' => $program['progression']['type'],
                        'increment_kg' => $program['progression']['increment_kg'],
                    ], fn ($v) => $v !== null),
                ]);
                foreach (array_values($exercise['sets']) as $si => $set) {
                    WorkoutSetTemplate::query()->create([
                        'workout_exercise_id' => $we->id,
                        'position' => $si + 1,
                        'set_type' => $set['set_type'],
                        'reps_min' => $set['reps_min'],
                        'reps_max' => $set['reps_max'],
                        'rpe' => $set['rpe'],
                        'duration_seconds' => $set['duration_seconds'],
                        'rest_seconds' => $set['rest_seconds'] ?? $exercise['rest_seconds'],
                    ]);
                }
            }
        }

        $workout->forceFill(['equipment_summary' => array_values(array_unique($equipment))])->save();

        return $workout;
    }

    /**
     * Removes server-added keys before schema validation.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function strip(array $payload): array
    {
        unset($payload['mapped_exercises'], $payload['request'], $payload['language']);

        return $payload;
    }
}
