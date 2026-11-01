<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Support\TextNormalizer;
use App\Domain\Workouts\Models\Exercise;
use Illuminate\Support\Collection;

/**
 * Retrieval over the tenant-visible exercise library (global + tenant's own).
 * Candidates are scored by equipment compatibility, target muscles, movement
 * patterns, level and name/alias overlap with the request, then interleaved
 * per pattern so every movement pattern is represented. Unknown exercise
 * names from a model are mapped to the closest library entry.
 */
final class ExerciseRetriever
{
    /** Equipment that never limits a selection. */
    private const ALWAYS_AVAILABLE = ['bodyweight', 'none', 'body_weight', 'mat', 'floor'];

    private const FULL_GYM = ['gym', 'full_gym', 'commercial_gym', 'all'];

    /** @var Collection<int, Exercise>|null */
    private ?Collection $library = null;

    /** @return Collection<int, Exercise> */
    public function library(): Collection
    {
        return $this->library ??= Exercise::query()
            ->where('is_published', true)
            ->get(['id', 'tenant_id', 'slug', 'name', 'aliases', 'primary_muscle', 'secondary_muscles', 'equipment', 'pattern', 'mechanics', 'level', 'is_bodyweight']);
    }

    public function find(string $id): ?Exercise
    {
        return $this->library()->firstWhere('id', $id);
    }

    /**
     * @param  array{equipment?: list<string>, muscles?: list<string>, patterns?: list<string>, level?: string|null, query?: string|null, exclude?: list<string>}  $criteria
     * @return list<array<string, mixed>>
     */
    public function candidates(array $criteria, int $limit = 60): array
    {
        $equipment = $this->normalizeEquipment($criteria['equipment'] ?? []);
        $queryTokens = TextNormalizer::tokens($criteria['query'] ?? '');
        $muscles = array_map('strtolower', $criteria['muscles'] ?? []);
        $patterns = array_map('strtolower', $criteria['patterns'] ?? []);
        $level = $criteria['level'] ?? null;
        $exclude = $criteria['exclude'] ?? [];

        $scored = $this->library()
            ->filter(fn (Exercise $e) => ! in_array($e->id, $exclude, true) && $this->equipmentAllowed($e, $equipment))
            ->map(function (Exercise $e) use ($queryTokens, $muscles, $patterns, $level) {
                $score = 1.0;
                if ($muscles !== [] && in_array(strtolower((string) $e->primary_muscle), $muscles, true)) {
                    $score += 3;
                }
                if ($muscles !== [] && array_intersect($muscles, array_map('strtolower', $e->secondary_muscles ?? [])) !== []) {
                    $score += 1;
                }
                if ($patterns !== [] && in_array(strtolower((string) $e->pattern), $patterns, true)) {
                    $score += 2;
                }
                if ($e->mechanics === 'compound') {
                    $score += 0.5;
                }
                $score += $this->levelFit((string) $e->level, $level);
                if ($queryTokens !== []) {
                    $score += 2 * count(array_intersect($queryTokens, $this->tokensFor($e)));
                }

                return ['exercise' => $e, 'score' => $score];
            })
            ->sortByDesc('score')
            ->values();

        // Interleave by movement pattern so the model can build balanced sessions.
        $byPattern = $scored->groupBy(fn (array $row) => $row['exercise']->pattern ?: 'other');
        $picked = [];
        while (count($picked) < $limit && $byPattern->isNotEmpty()) {
            foreach ($byPattern->keys() as $key) {
                $bucket = $byPattern->get($key);
                $row = $bucket->shift();
                if ($row !== null) {
                    $picked[] = $row['exercise'];
                }
                $bucket->isEmpty() ? $byPattern->forget($key) : $byPattern->put($key, $bucket);
                if (count($picked) >= $limit) {
                    break;
                }
            }
        }

        return array_map(fn (Exercise $e) => $this->describe($e), $picked);
    }

    /**
     * Closest library exercise for a free-text name (any language).
     *
     * @param  list<string>  $equipment
     */
    public function closest(string $name, array $equipment = [], float $threshold = 0.5): ?Exercise
    {
        $allowed = $this->normalizeEquipment($equipment);
        $target = TextNormalizer::tokens($name);
        if ($target === []) {
            return null;
        }
        $best = null;
        $bestScore = 0.0;
        foreach ($this->library() as $exercise) {
            if (! $this->equipmentAllowed($exercise, $allowed)) {
                continue;
            }
            foreach ($this->names($exercise) as $candidate) {
                $tokens = TextNormalizer::tokens($candidate);
                if ($tokens === []) {
                    continue;
                }
                $score = 2 * count(array_intersect($target, $tokens)) / (count($target) + count($tokens));
                if ($score > $bestScore) {
                    [$best, $bestScore] = [$exercise, $score];
                }
            }
        }

        return $bestScore >= $threshold ? $best : null;
    }

    /** @return array<string, mixed> */
    public function describe(Exercise $e): array
    {
        return [
            'id' => $e->id,
            'name' => $e->translate('name', 'en') ?? $e->slug,
            'primary_muscle' => $e->primary_muscle,
            'pattern' => $e->pattern,
            'equipment' => array_values($e->equipment ?? []),
            'mechanics' => $e->mechanics,
            'level' => $e->level,
        ];
    }

    /**
     * @param  list<string>  $equipment
     * @return list<string>|null null = no restriction
     */
    public function normalizeEquipment(array $equipment): ?array
    {
        if ($equipment === []) {
            return null;
        }
        $out = [];
        foreach ($equipment as $item) {
            $key = self::equipmentKey((string) $item);
            if (in_array($key, self::FULL_GYM, true)) {
                return null;
            }
            $out[] = $key;
        }

        return array_values(array_unique($out));
    }

    /** Canonical equipment key (singular, synonyms in en/fr/es/ar folded). */
    public static function equipmentKey(string $item): string
    {
        $key = str_replace(' ', '_', TextNormalizer::normalize($item));
        $synonyms = [
            'dumbbell' => ['dumbbells', 'halteres', 'haltere', 'mancuerna', 'mancuernas', 'دمبل', 'دمبلز', 'دامبل'],
            'barbell' => ['barbells', 'barre', 'barra', 'بار', 'بار_حديد'],
            'kettlebell' => ['kettlebells', 'pesa_rusa', 'كيتل_بل'],
            'band' => ['bands', 'resistance_band', 'resistance_bands', 'elastique', 'elastiques', 'banda', 'bandas', 'حبل_مطاطي'],
            'bodyweight' => ['body_weight', 'poids_du_corps', 'peso_corporal', 'وزن_الجسم', 'no_equipment', 'sans_materiel', 'sin_equipo'],
            'machine' => ['machines', 'maquina', 'maquinas', 'جهاز', 'اجهزه'],
            'cable' => ['cables', 'poulie', 'polea', 'كابل'],
            'bench' => ['benches', 'banc', 'banco', 'بنش'],
            'pull_up_bar' => ['pullup_bar', 'barre_de_traction', 'barra_de_dominadas'],
            'gym' => ['full_gym', 'salle', 'gimnasio', 'جيم', 'نادي'],
        ];
        foreach ($synonyms as $canonical => $list) {
            if ($key === $canonical || in_array($key, $list, true)) {
                return $canonical;
            }
        }

        return (string) (preg_replace('/(?<=[a-z]{3})s$/', '', $key) ?: $key);
    }

    /** @param list<string>|null $allowed */
    public function equipmentAllowed(Exercise $e, ?array $allowed): bool
    {
        if ($allowed === null) {
            return true;
        }
        foreach ($e->equipment ?? [] as $item) {
            $key = self::equipmentKey((string) $item);
            if (! in_array($key, self::ALWAYS_AVAILABLE, true) && ! in_array($key, $allowed, true)) {
                return false;
            }
        }

        return true;
    }

    private function levelFit(string $exerciseLevel, ?string $level): float
    {
        $rank = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
        if ($level === null || ! isset($rank[$level], $rank[$exerciseLevel])) {
            return 0.0;
        }

        return $rank[$exerciseLevel] <= $rank[$level] ? 0.5 : -1.5;
    }

    /** @return list<string> */
    private function names(Exercise $e): array
    {
        $names = [(string) str_replace('-', ' ', (string) $e->slug)];
        foreach ((array) $e->name as $value) {
            if (is_string($value)) {
                $names[] = $value;
            }
        }
        $aliases = (array) ($e->aliases ?? []);
        array_walk_recursive($aliases, function ($v) use (&$names) {
            if (is_string($v)) {
                $names[] = $v;
            }
        });

        return $names;
    }

    /** @return list<string> */
    private function tokensFor(Exercise $e): array
    {
        $tokens = [];
        foreach ($this->names($e) as $n) {
            array_push($tokens, ...TextNormalizer::tokens($n));
        }

        return array_values(array_unique($tokens));
    }
}
