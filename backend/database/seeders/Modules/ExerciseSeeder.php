<?php

namespace Database\Seeders\Modules;

use App\Domain\Workouts\Models\Equipment;
use App\Domain\Workouts\Models\MuscleGroup;
use App\Domain\Workouts\Services\ExerciseImporter;
use Illuminate\Database\Seeder;

/**
 * Curated global exercise library (tenant_id null): muscle groups, equipment and
 * 180+ exercises with names/aliases in en, ar, fr, es, instructions, tips, mistakes,
 * alternatives and calisthenics progression chains. Idempotent (upserts by key/slug).
 */
class ExerciseSeeder extends Seeder
{
    public function run(ExerciseImporter $importer): void
    {
        $taxonomy = require database_path('data/exercise_taxonomy.php');

        foreach ($taxonomy['muscle_groups'] as $key => [$region, $en, $ar, $fr, $es]) {
            $row = MuscleGroup::query()->firstOrNew(['key' => $key]);
            $row->fill(['region' => $region, 'name' => ['en' => $en, 'ar' => $ar, 'fr' => $fr, 'es' => $es]])->save();
        }
        foreach ($taxonomy['equipment'] as $key => [$en, $ar, $fr, $es]) {
            $row = Equipment::query()->firstOrNew(['key' => $key]);
            $row->fill(['name' => ['en' => $en, 'ar' => $ar, 'fr' => $fr, 'es' => $es]])->save();
        }

        $definitions = require database_path('data/exercises.php');
        $result = $importer->import($definitions);

        $this->command?->info("Exercise library: {$result['created']} created, {$result['updated']} updated.");
    }
}
