<?php

namespace App\Domain\Workouts\Console;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Services\ExerciseImporter;
use Illuminate\Console\Command;

/**
 * Bulk import exercises from CSV (UTF-8, header row, comma separated).
 *
 * Columns (only `slug` or `name_en` is required; unknown columns are ignored):
 *   slug, name_en, name_ar, name_fr, name_es,
 *   aliases_en, aliases_ar, aliases_fr, aliases_es            (pipe "|" separated)
 *   primary_muscle, secondary_muscles (|), equipment (|)       (muscle_groups.key / equipment.key)
 *   pattern, mechanics, force, level, is_unilateral, is_bodyweight (1/0, true/false, yes/no)
 *   metrics (| of weight,reps,time,distance,calories,rpe)
 *   instructions_en, instructions_ar, instructions_fr, instructions_es (| separated steps)
 *   tips_en … tips_es, mistakes_en … mistakes_es             (| separated)
 *   alternatives (| of slugs), progression_parent (slug), progression_step, video_url (YouTube/Vimeo)
 *
 * Rows upsert by slug into the global library, or a tenant library with --tenant=<id|slug>.
 */
class ImportExercisesCommand extends Command
{
    protected $signature = 'exercises:import {csv : Path to the CSV file} {--tenant= : Tenant id or slug (default: global library)} {--dry-run : Validate only}';

    protected $description = 'Bulk import exercises (4-language names, aliases, instructions) from a CSV file';

    private const LIST_COLUMNS = ['secondary_muscles', 'equipment', 'metrics', 'alternatives'];

    private const LOCALES = ['en', 'ar', 'fr', 'es'];

    public function handle(ExerciseImporter $importer): int
    {
        $path = (string) $this->argument('csv');
        if (! is_readable($path)) {
            $this->error("Cannot read {$path}");

            return self::FAILURE;
        }
        $tenantId = null;
        if ($ref = $this->option('tenant')) {
            $tenant = Tenant::query()->where('id', $ref)->orWhere('slug', $ref)->first();
            if (! $tenant) {
                $this->error("Unknown tenant {$ref}");

                return self::FAILURE;
            }
            $tenantId = $tenant->id;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, escape: '\\');
        if (! $header) {
            $this->error('Empty CSV.');

            return self::FAILURE;
        }
        $header = array_map(fn ($h) => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);

        $definitions = [];
        $errors = [];
        $line = 1;
        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $line++;
            if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $data = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), ''));
            $def = $this->toDefinition($data);
            if (empty($def['slug']) && empty($def['name']['en'])) {
                $errors[] = "Line {$line}: slug or name_en is required.";

                continue;
            }
            $definitions[] = $def;
        }
        fclose($handle);

        foreach ($errors as $error) {
            $this->warn($error);
        }
        if ($this->option('dry-run')) {
            $this->info(count($definitions).' valid rows, '.count($errors).' errors (dry run).');

            return $errors === [] ? self::SUCCESS : self::FAILURE;
        }

        $result = $importer->import($definitions, $tenantId);
        $this->info("Imported: {$result['created']} created, {$result['updated']} updated, ".count($errors).' skipped.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    private function toDefinition(array $data): array
    {
        $list = fn (?string $v) => array_values(array_filter(array_map('trim', explode('|', (string) $v)), fn ($x) => $x !== ''));
        $bool = fn (?string $v) => in_array(strtolower(trim((string) $v)), ['1', 'true', 'yes', 'y'], true);
        $def = ['slug' => trim($data['slug'] ?? '') ?: null];
        foreach (self::LOCALES as $l) {
            $def['name'][$l] = trim($data['name_'.$l] ?? '');
            foreach (['aliases', 'instructions', 'tips', 'mistakes'] as $field) {
                $def[$field][$l] = $list($data[$field.'_'.$l] ?? '');
            }
        }
        foreach (['primary_muscle', 'pattern', 'mechanics', 'force', 'level', 'progression_parent', 'video_url'] as $field) {
            if (trim($data[$field] ?? '') !== '') {
                $def[$field] = trim($data[$field]);
            }
        }
        foreach (self::LIST_COLUMNS as $field) {
            if (array_key_exists($field, $data)) {
                $def[$field] = $list($data[$field]);
            }
        }
        if (($data['metrics'] ?? '') === '') {
            unset($def['metrics']);
        }
        foreach (['is_unilateral', 'is_bodyweight'] as $field) {
            $def[$field] = $bool($data[$field] ?? '');
        }
        if (trim($data['progression_step'] ?? '') !== '') {
            $def['progression_step'] = (int) $data['progression_step'];
        }

        return $def;
    }
}
