<?php

/*
| Curated global exercise library (seeded by Database\Seeders\Modules\ExerciseSeeder).
| Each category file in ./exercises returns a list built with $e():
|   $e(slug, [en, ar, fr, es], primary_muscle, secondary_muscles, equipment, pattern, mechanics, force, level, opts)
| opts: aliases {locale: [..]}, uni (unilateral), bw (bodyweight), metrics [..],
|       steps {locale: [..]}, tip {locale: string}, mistake {locale: string},
|       alt [slugs] (alternatives), parent (progression parent slug), step (progression step).
*/

$e = static function (string $slug, array $names, ?string $primary, array $secondary, array $equipment, string $pattern, ?string $mechanics, ?string $force, string $level, array $opts = []): array {
    [$en, $ar, $fr, $es] = $names;
    $wrap = static fn (array $byLocale): array => array_map(static fn ($v) => (array) $v, $byLocale);
    $bw = (bool) ($opts['bw'] ?? false);

    return [
        'slug' => $slug,
        'name' => ['en' => $en, 'ar' => $ar, 'fr' => $fr, 'es' => $es],
        'aliases' => $opts['aliases'] ?? [],
        'primary_muscle' => $primary,
        'secondary_muscles' => $secondary,
        'equipment' => $equipment,
        'pattern' => $pattern,
        'mechanics' => $mechanics,
        'force' => $force,
        'level' => $level,
        'is_unilateral' => (bool) ($opts['uni'] ?? false),
        'is_bodyweight' => $bw,
        'metrics' => $opts['metrics'] ?? ($bw ? ['reps'] : ['weight', 'reps']),
        'instructions' => $opts['steps'] ?? [],
        'tips' => $wrap($opts['tip'] ?? []),
        'mistakes' => $wrap($opts['mistake'] ?? []),
        'alternatives' => $opts['alt'] ?? [],
        'progression_parent' => $opts['parent'] ?? null,
        'progression_step' => $opts['step'] ?? null,
    ];
};

$all = [];
foreach (glob(__DIR__.'/exercises/*.php') ?: [] as $file) {
    $all = array_merge($all, require $file);
}

return $all;
