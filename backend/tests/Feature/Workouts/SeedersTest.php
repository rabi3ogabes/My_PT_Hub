<?php

use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\Workout;
use Database\Seeders\Modules\ExerciseSeeder;
use Database\Seeders\Modules\ProgramTemplateSeeder;

it('seeds a 4-language global exercise library with progressions and alternatives', function () {
    $this->seed(ExerciseSeeder::class);

    expect(Exercise::whereNull('tenant_id')->count())->toBeGreaterThanOrEqual(180);
    $bench = Exercise::where('slug', 'dumbbell-bench-press')->first();
    expect($bench->name)->toHaveKeys(['en', 'ar', 'fr', 'es'])
        ->and($bench->instructions)->toHaveKeys(['en', 'ar', 'fr', 'es'])
        ->and($bench->alternatives()->count())->toBeGreaterThan(0);
    expect(Exercise::whereNull('tenant_id')->get()->filter(fn ($e) => count($e->instructions ?? []) === 4)->count())->toBeGreaterThanOrEqual(60);
    expect(Exercise::where('slug', 'one-arm-push-up')->first()->progressionParent->slug)->toBe('archer-push-up');

    // Idempotent re-run.
    $this->seed(ExerciseSeeder::class);
    expect(Exercise::whereNull('tenant_id')->where('slug', 'push-up')->count())->toBe(1);
});

it('seeds at least 25 public program templates with real workouts', function () {
    $this->seed(ProgramTemplateSeeder::class);

    $programs = Program::whereNull('tenant_id')->where('visibility', 'public')->where('is_template', true)->with('weeks.days')->get();
    expect($programs->count())->toBeGreaterThanOrEqual(25);
    foreach ($programs as $program) {
        expect($program->name)->toHaveKeys(['en', 'ar', 'fr', 'es'])
            ->and($program->description)->toHaveKeys(['en', 'ar', 'fr', 'es'])
            ->and($program->weeks->count())->toBeGreaterThan(0)
            ->and($program->weeks->flatMap->days->whereNotNull('workout_id')->count())->toBeGreaterThan(0);
    }
    $tabata = Workout::whereNull('tenant_id')->where('name', 'Home Tabata Burner')->with('blocks')->first();
    expect($tabata->blocks->pluck('type'))->toContain('tabata')->and($tabata->estimated_minutes)->toBeGreaterThan(5);

    $this->seed(ProgramTemplateSeeder::class);
    expect(Program::whereNull('tenant_id')->count())->toBe($programs->count());
    expect($this->getJson('/api/v1/public/program-templates?per_page=100')->assertOk()->json('data'))->toHaveCount($programs->count());
});

it('imports exercises from CSV', function () {
    $csv = sys_get_temp_dir().'/aurum-ex-'.uniqid().'.csv';
    file_put_contents($csv, "slug,name_en,name_ar,name_fr,name_es,aliases_en,aliases_ar,primary_muscle,equipment,level,is_bodyweight,instructions_en,alternatives\n"
        ."zercher-squat,Zercher Squat,سكوات زيرشر,Squat Zercher,Sentadilla Zercher,zercher|elbow squat,زيرشر,quads,barbell,advanced,0,Hold the bar in your elbows.|Squat down.|Stand up.,\n"
        ."ring-row,Ring Row,تجديف الحلقات,Rowing aux anneaux,Remo en anillas,,,upper_back,rings,beginner,yes,,zercher-squat\n");

    $this->artisan('exercises:import', ['csv' => $csv])->assertSuccessful();
    @unlink($csv);

    $z = Exercise::where('slug', 'zercher-squat')->first();
    expect($z->name['ar'])->toBe('سكوات زيرشر')
        ->and($z->aliases['en'])->toBe(['zercher', 'elbow squat'])
        ->and($z->instructions['en'])->toHaveCount(3)
        ->and(Exercise::where('slug', 'ring-row')->first()->is_bodyweight)->toBeTrue()
        ->and(Exercise::where('slug', 'ring-row')->first()->alternatives->pluck('slug')->all())->toBe(['zercher-squat']);
});
