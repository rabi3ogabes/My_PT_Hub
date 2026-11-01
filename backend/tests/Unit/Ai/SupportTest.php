<?php

use App\Domain\Ai\Services\ChurnScorer;
use App\Domain\Ai\Services\MacroBalancer;
use App\Domain\Ai\Support\CostCalculator;
use App\Domain\Ai\Support\PiiRedactor;
use App\Domain\Ai\Support\PromptLibrary;
use App\Domain\Ai\Support\RequestParser;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Ai\Support\SchemaValidator;

it('validates structured outputs and reports paths', function () {
    $v = new SchemaValidator;
    expect($v->validate(['reply' => 'Hi', 'language' => 'en'], Schemas::replyDraft()))->toBe([]);

    $errors = $v->validate(['reply' => '', 'language' => 'de', 'extra' => 1], Schemas::replyDraft());
    expect(implode("\n", $errors))
        ->toContain('$.reply: must be at least 1 characters')
        ->toContain('$.language: must be one of')
        ->toContain('$.extra: unknown property');

    expect($v->validate(['summary' => 'x'], Schemas::progression()))->toContain('$.suggestions: is required');
});

it('builds strict provider schemas without unsupported keywords', function () {
    $schema = (new SchemaValidator)->forProvider(Schemas::program());
    $json = json_encode($schema);
    expect($json)->not->toContain('maxLength')->not->toContain('minimum')
        ->and($schema['additionalProperties'])->toBeFalse()
        ->and($schema['required'])->toContain('days');
});

it('renders every versioned prompt template and rejects missing variables', function () {
    $library = new PromptLibrary;
    foreach (glob(resource_path('prompts/*/v1.md')) as $file) {
        preg_match_all('/\{\{\s*([a-z0-9_]+)\s*\}\}/', file_get_contents($file), $m);
        $vars = array_fill_keys($m[1], ['x' => 1]);
        $rendered = $library->render(basename(dirname($file)), 'v1', $vars);
        expect($rendered['system'])->not->toBeEmpty()->and($rendered['user'])->not->toContain('{{');
    }

    expect(fn () => $library->render('reply_draft', 'v1', []))->toThrow(InvalidArgumentException::class, 'missing variable');
});

it('redacts contact details but keeps training numbers', function () {
    $text = PiiRedactor::redact('Email me at omar@example.com or call +974 5555 1234. I did 3 sets of 10 at 60 kg, targets 2000 150 200 67.');
    expect($text)->not->toContain('omar@example.com')->not->toContain('5555 1234')
        ->toContain('[email]')->toContain('[phone]')
        ->toContain('3 sets of 10 at 60 kg')->toContain('2000 150 200 67');
});

it('parses free-text program requests in four languages', function () {
    expect(RequestParser::program('4-day upper lower for a busy dad with dumbbells only, 45 min'))
        ->toMatchArray(['days_per_week' => 4, 'session_minutes' => 45, 'equipment' => ['dumbbell']])
        ->and(RequestParser::program('Programme de 3 jours pour débutant avec haltères')['days_per_week'])->toBe(3)
        ->and(RequestParser::program('Programme de 3 jours pour débutant avec haltères')['level'])->toBe('beginner')
        ->and(RequestParser::program('Plan de 5 días de hipertrofia con mancuernas')['goal'])->toBe('muscle_gain')
        ->and(RequestParser::program('برنامج ٤ ايام تضخيم بالدمبل')['days_per_week'])->toBe(4);
});

it('balances a day to within ±5% of every macro target', function () {
    $items = [
        ['quantity' => 150, 'unit' => 'g', 'per' => ['kcal' => 1.65, 'protein_g' => 0.31, 'carbs_g' => 0, 'fat_g' => 0.036]],
        ['quantity' => 200, 'unit' => 'g', 'per' => ['kcal' => 1.30, 'protein_g' => 0.027, 'carbs_g' => 0.28, 'fat_g' => 0.003]],
        ['quantity' => 10, 'unit' => 'g', 'per' => ['kcal' => 8.84, 'protein_g' => 0, 'carbs_g' => 0, 'fat_g' => 1.0]],
        ['quantity' => 100, 'unit' => 'g', 'per' => ['kcal' => 0.59, 'protein_g' => 0.10, 'carbs_g' => 0.036, 'fat_g' => 0.004]],
        ['quantity' => 60, 'unit' => 'g', 'per' => ['kcal' => 3.89, 'protein_g' => 0.13, 'carbs_g' => 0.66, 'fat_g' => 0.07]],
    ];
    $target = ['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67];

    $result = (new MacroBalancer)->balance($items, $target);

    expect($result['ok'])->toBeTrue();
    foreach ($result['deviations'] as $metric => $dev) {
        expect(abs($dev))->toBeLessThanOrEqual(0.05, $metric);
    }
    expect(MacroBalancer::group(['protein_g' => 0.31, 'carbs_g' => 0, 'fat_g' => 0.036]))->toBe('protein')
        ->and(MacroBalancer::group(['protein_g' => 0, 'carbs_g' => 0, 'fat_g' => 1]))->toBe('fat');
});

it('reports days that cannot be balanced', function () {
    $items = [['quantity' => 100, 'unit' => 'g', 'per' => ['kcal' => 1.3, 'protein_g' => 0.027, 'carbs_g' => 0.28, 'fat_g' => 0.003]]];
    $result = (new MacroBalancer)->balance($items, ['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67]);
    expect($result['ok'])->toBeFalse();
});

it('scores churn transparently with weighted factors', function () {
    $scorer = new ChurnScorer;
    $healthy = $scorer->score(['days_since_active' => 1, 'sessions_last_14' => 6, 'sessions_prev_14' => 6, 'compliance_score' => 90, 'compliance_trend' => null, 'negative_sentiment' => 0.0, 'overdue_checkins' => 0, 'payment_status' => 'ok', 'unanswered_coach_messages' => 0, 'avg_reply_hours' => 2.0]);
    $atRisk = $scorer->score(['days_since_active' => 20, 'sessions_last_14' => 0, 'sessions_prev_14' => 5, 'compliance_score' => 30, 'compliance_trend' => -10.0, 'negative_sentiment' => 0.7, 'overdue_checkins' => 2, 'payment_status' => 'past_due', 'unanswered_coach_messages' => 4, 'avg_reply_hours' => null]);

    expect($healthy['level'])->toBe('green')->and($healthy['score'])->toBeLessThan(15)
        ->and($atRisk['level'])->toBe('red')->and($atRisk['score'])->toBeGreaterThan(85)
        ->and($atRisk['reasons'])->toContain('recency')->toContain('payment')
        ->and(array_sum(array_column($atRisk['factors'], 'weight')))->toEqualWithDelta(1.0, 0.0001)
        ->and(round(array_sum(array_column($atRisk['factors'], 'contribution'))))->toEqual((float) $atRisk['score']);
});

it('computes cost in micros from model pricing', function () {
    expect(CostCalculator::micros('claude-sonnet-5-5', 1000, 500))->toBe(7000)
        ->and(CostCalculator::micros('fake', 1000, 500))->toBe(0)
        ->and(CostCalculator::formatUsd(7000))->toBe('$0.0070');
});
