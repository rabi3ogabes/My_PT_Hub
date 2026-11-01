<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Support\TextNormalizer;
use App\Domain\Nutrition\Models\Food;
use Illuminate\Support\Collection;

/** Maps free-text food names (any of the 4 languages) to tenant-visible foods. */
final class FoodMatcher
{
    /** @return array{food: Food, score: float}|null */
    public function match(string $name, float $threshold = 0.5): ?array
    {
        $tokens = TextNormalizer::tokens($name);
        if ($tokens === []) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;
        foreach ($this->candidates($tokens) as $food) {
            $score = $this->score($tokens, $food);
            if ($score > $bestScore || ($score === $bestScore && $best && $food->is_verified && ! $best->is_verified)) {
                [$best, $bestScore] = [$food, $score];
            }
        }

        return $best && $bestScore >= $threshold ? ['food' => $best, 'score' => round($bestScore, 3)] : null;
    }

    /**
     * @param  list<string>  $tokens
     * @return Collection<int, Food>
     */
    private function candidates(array $tokens): Collection
    {
        $terms = collect($tokens)->sortByDesc(fn (string $t) => mb_strlen($t))->take(3)->values();
        $found = Food::query()
            ->where('moderation', 'approved')
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.str_replace(['%', '_'], ' ', $term).'%';
                    $q->orWhere('search_text', 'like', $like)->orWhere('name', 'like', $like);
                }
            })
            ->limit(300)
            ->get();

        return $found->isNotEmpty() ? $found : Food::query()->where('moderation', 'approved')->limit(2000)->get();
    }

    /** @param list<string> $tokens */
    private function score(array $tokens, Food $food): float
    {
        $names = [];
        foreach ((array) $food->name as $v) {
            if (is_string($v)) {
                $names[] = $v;
            }
        }
        $aliases = (array) ($food->aliases ?? []);
        array_walk_recursive($aliases, function ($v) use (&$names) {
            if (is_string($v)) {
                $names[] = $v;
            }
        });

        $best = 0.0;
        foreach ($names as $n) {
            $nt = TextNormalizer::tokens($n);
            if ($nt === []) {
                continue;
            }
            $common = count(array_intersect($tokens, $nt));
            $dice = 2 * $common / (count($tokens) + count($nt));
            // A query fully contained in a food name ("rice" in "white rice cooked") is a strong match.
            $containment = $common === count($tokens) ? 0.75 + 0.25 * $dice : 0.0;
            $best = max($best, $dice, $containment);
        }

        return $best;
    }
}
