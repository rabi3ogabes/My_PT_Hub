<?php

namespace App\Domain\Ai\Support;

/** Token cost in USD micros using config('ai.pricing') ($ per 1M tokens). */
final class CostCalculator
{
    public static function micros(string $model, int $tokensIn, int $tokensOut): int
    {
        $pricing = config('ai.pricing', []);
        $price = $pricing[$model] ?? null;
        if ($price === null) {
            foreach ($pricing as $key => $p) {
                if ($key !== 'default' && str_starts_with($model, $key)) {
                    $price = $p;
                    break;
                }
            }
        }
        $price ??= $pricing['default'] ?? ['in' => 0, 'out' => 0];

        return (int) round($tokensIn * (float) $price['in'] + $tokensOut * (float) $price['out']);
    }

    public static function formatUsd(int $micros): string
    {
        return '$'.number_format($micros / 1_000_000, 4);
    }
}
