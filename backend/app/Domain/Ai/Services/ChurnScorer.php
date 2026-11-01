<?php

namespace App\Domain\Ai\Services;

/**
 * Transparent churn score (0-100) from weighted, explainable risk factors.
 * Each factor yields a risk in [0, 1]; the score is the weighted sum. The
 * factor breakdown is returned so coaches see exactly why a client is at risk.
 */
final class ChurnScorer
{
    public const WEIGHTS = [
        'recency' => 0.25,
        'frequency' => 0.20,
        'compliance' => 0.20,
        'sentiment' => 0.10,
        'payment' => 0.15,
        'response' => 0.10,
    ];

    public const RED = 60;

    public const AMBER = 35;

    /**
     * @param  array{days_since_active: int|null, sessions_last_14: int, sessions_prev_14: int, compliance_score: int|null, compliance_trend: float|null, negative_sentiment: float, overdue_checkins: int, payment_status: string, unanswered_coach_messages: int, avg_reply_hours: float|null}  $s
     * @return array{score: int, level: string, factors: list<array{factor: string, weight: float, risk: float, contribution: float, value: mixed}>, reasons: list<string>}
     */
    public function score(array $s): array
    {
        $risks = [
            'recency' => [$s['days_since_active'] === null ? 1.0 : min(1.0, $s['days_since_active'] / 14), $s['days_since_active']],
            'frequency' => [$this->frequencyRisk($s['sessions_last_14'], $s['sessions_prev_14']), ['last_14' => $s['sessions_last_14'], 'previous_14' => $s['sessions_prev_14']]],
            'compliance' => [$this->complianceRisk($s['compliance_score'], $s['compliance_trend']), $s['compliance_score']],
            'sentiment' => [min(1.0, $s['negative_sentiment'] + 0.25 * $s['overdue_checkins']), ['negative' => $s['negative_sentiment'], 'overdue_checkins' => $s['overdue_checkins']]],
            'payment' => [match ($s['payment_status']) {
                'past_due', 'failed' => 1.0,
                'cancelling' => 0.8,
                default => 0.0,
            }, $s['payment_status']],
            'response' => [max(min(1.0, $s['unanswered_coach_messages'] / 3), $s['avg_reply_hours'] === null ? 0.0 : min(1.0, $s['avg_reply_hours'] / 72)), ['unanswered' => $s['unanswered_coach_messages'], 'avg_reply_hours' => $s['avg_reply_hours']]],
        ];

        $factors = [];
        $total = 0.0;
        foreach (self::WEIGHTS as $factor => $weight) {
            [$risk, $value] = $risks[$factor];
            $risk = round(max(0.0, min(1.0, $risk)), 3);
            $contribution = round($weight * $risk * 100, 1);
            $total += $contribution;
            $factors[] = ['factor' => $factor, 'weight' => $weight, 'risk' => $risk, 'contribution' => $contribution, 'value' => $value];
        }
        $score = (int) round($total);
        $reasons = collect($factors)->filter(fn ($f) => $f['risk'] >= 0.5)->sortByDesc('contribution')->pluck('factor')->values()->all();

        return [
            'score' => $score,
            'level' => $score >= self::RED ? 'red' : ($score >= self::AMBER ? 'amber' : 'green'),
            'factors' => $factors,
            'reasons' => $reasons,
        ];
    }

    private function frequencyRisk(int $last, int $previous): float
    {
        $base = match (true) {
            $last >= 3 => 0.0,
            $last === 2 => 0.25,
            $last === 1 => 0.5,
            default => 1.0,
        };
        $drop = $previous > 0 ? max(0.0, ($previous - $last) / $previous) : 0.0;

        return max($base, $drop);
    }

    private function complianceRisk(?int $score, ?float $trend): float
    {
        $risk = $score === null ? 0.5 : (100 - $score) / 100;
        if ($trend !== null && $trend < 0) {
            $risk += min(0.5, -$trend / 50);
        }

        return min(1.0, $risk);
    }
}
