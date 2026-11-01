<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Workouts\Models\Exercise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Upserts exercise definitions (seed data file or CSV rows) into the global
 * library (tenant_id null) or a tenant library, keyed by slug, then links
 * alternatives and calisthenics progression chains by slug.
 *
 * Definition keys: slug, name{locale}, aliases{locale: []}, primary_muscle, secondary_muscles[],
 * equipment[], pattern, mechanics, force, level, is_unilateral, is_bodyweight, metrics[],
 * instructions{locale: []}, tips{locale: []}, mistakes{locale: []}, alternatives[slug],
 * progression_parent (slug), progression_step, video_url.
 */
class ExerciseImporter
{
    /**
     * @param  list<array<string, mixed>>  $definitions
     * @return array{created: int, updated: int}
     */
    public function import(array $definitions, ?string $tenantId = null): array
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($definitions, $tenantId, &$created, &$updated) {
            $ids = [];
            foreach ($definitions as $i => $def) {
                $exercise = $this->upsert($def, $tenantId);
                $definitions[$i]['slug'] = $exercise->slug;
                $exercise->wasRecentlyCreated ? $created++ : $updated++;
                $ids[$exercise->slug] = $exercise->id;
            }

            $lookup = fn (string $slug) => $ids[$slug] ?? $this->scoped($tenantId)->where('slug', $slug)->value('id')
                ?? ($tenantId ? Exercise::withoutGlobalScopes()->whereNull('tenant_id')->where('slug', $slug)->value('id') : null);

            foreach ($definitions as $def) {
                $id = $ids[$def['slug']];
                if (! empty($def['progression_parent'])) {
                    Exercise::withoutGlobalScopes()->whereKey($id)->update([
                        'progression_parent_id' => $lookup((string) $def['progression_parent']),
                        'progression_step' => $def['progression_step'] ?? null,
                    ]);
                } elseif (isset($def['progression_step'])) {
                    Exercise::withoutGlobalScopes()->whereKey($id)->update(['progression_step' => $def['progression_step']]);
                }
                if (array_key_exists('alternatives', $def)) {
                    $alt = collect($def['alternatives'] ?? [])->map(fn ($slug) => $lookup((string) $slug))->filter()->reject(fn ($a) => $a === $id)->unique()->values();
                    DB::table('exercise_alternatives')->where('exercise_id', $id)->delete();
                    DB::table('exercise_alternatives')->insert($alt->map(fn ($a) => ['exercise_id' => $id, 'alternative_id' => $a])->all());
                }
            }
        });

        return ['created' => $created, 'updated' => $updated];
    }

    /** @param array<string, mixed> $def */
    public function upsert(array $def, ?string $tenantId = null): Exercise
    {
        $slug = Str::slug((string) ($def['slug'] ?? ($def['name']['en'] ?? '')));
        if ($slug === '') {
            throw new \InvalidArgumentException('Exercise definition needs a slug or an English name.');
        }
        $exercise = $this->scoped($tenantId)->withTrashed()->where('slug', $slug)->first() ?? new Exercise;
        $exercise->tenant_id = $tenantId;
        $exercise->slug = $slug;
        $exercise->fill([
            'name' => $this->localized($def['name'] ?? []),
            'aliases' => $this->localizedLists($def['aliases'] ?? []),
            'instructions' => $this->localizedLists($def['instructions'] ?? []) ?: null,
            'tips' => $this->localizedLists($def['tips'] ?? []) ?: null,
            'mistakes' => $this->localizedLists($def['mistakes'] ?? []) ?: null,
            'primary_muscle' => $def['primary_muscle'] ?? null,
            'secondary_muscles' => array_values($def['secondary_muscles'] ?? []),
            'equipment' => array_values($def['equipment'] ?? []),
            'pattern' => $def['pattern'] ?? null,
            'mechanics' => $def['mechanics'] ?? null,
            'force' => $def['force'] ?? null,
            'level' => $def['level'] ?? 'beginner',
            'is_unilateral' => (bool) ($def['is_unilateral'] ?? false),
            'is_bodyweight' => (bool) ($def['is_bodyweight'] ?? false),
            'metrics' => array_values($def['metrics'] ?? (($def['is_bodyweight'] ?? false) ? ['reps'] : ['weight', 'reps'])),
            'is_published' => true,
            'deleted_at' => null,
        ]);
        if (! empty($def['video_url']) && ($embed = ExerciseLibrary::parseEmbed((string) $def['video_url']))) {
            $exercise->fill([
                'video_provider' => $embed['provider'], 'video_url' => $embed['url'],
                'poster_url' => $embed['poster_url'], 'video_status' => 'ready',
            ]);
        }
        $exercise->save();

        return $exercise;
    }

    private function scoped(?string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        $q = Exercise::withoutGlobalScopes();

        return $tenantId === null ? $q->whereNull('tenant_id') : $q->where('tenant_id', $tenantId);
    }

    /** @return array<string, string> */
    private function localized(array $values): array
    {
        return array_filter(array_intersect_key($values, array_flip(\App\Domain\Workouts\Support\Translatable::LOCALES)), fn ($v) => is_string($v) && trim($v) !== '');
    }

    /** @return array<string, list<string>> */
    private function localizedLists(array $values): array
    {
        $out = [];
        foreach (\App\Domain\Workouts\Support\Translatable::LOCALES as $locale) {
            $list = array_values(array_filter((array) ($values[$locale] ?? []), fn ($v) => is_string($v) && trim($v) !== ''));
            if ($list !== []) {
                $out[$locale] = $list;
            }
        }

        return $out;
    }
}
