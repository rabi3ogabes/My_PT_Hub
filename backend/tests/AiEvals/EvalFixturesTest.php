<?php

/*
| AI eval suite: runs every JSON fixture in tests/AiEvals/fixtures against
| the configured AI driver (Fake by default; set AI_EVALS_DRIVER=anthropic
| or openai with keys for the nightly real-provider run — see README.md)
| and checks the expected properties.
*/

use App\Domain\Ai\Providers\FakeProvider;
use App\Domain\Ai\Services\PriorityDetector;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Workouts\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Ai\Support\AiFixtures;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class, AiFixtures::class);

beforeEach(function () {
    if ($driver = env('AI_EVALS_DRIVER')) {
        config(['services.ai.driver' => $driver]);
    }
    app(FakeProvider::class)->reset();
});

dataset('ai_fixtures', function () {
    $out = [];
    foreach (glob(__DIR__.'/fixtures/*/*.json') as $file) {
        $fixture = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $out[$fixture['id']] = [$fixture];
    }
    ksort($out);

    return $out;
});

it('has at least 50 fixtures covering every evaluated feature', function () {
    $files = glob(__DIR__.'/fixtures/*/*.json');
    $features = array_unique(array_map(fn ($f) => basename(dirname($f)), $files));

    expect(count($files))->toBeGreaterThanOrEqual(50)
        ->and($features)->toContain('safety', 'priority', 'program', 'meal_plan', 'business_ask', 'content', 'photo_food');
});

it('meets the expected properties', function (array $fixture) {
    $input = $fixture['input'];
    $expect = $fixture['expect'];

    switch ($fixture['feature']) {
        case 'safety':
            $result = app(SafetyGuard::class)->screenText($input['text']);
            if (array_key_exists('flagged', $expect)) {
                expect($result->flagged())->toBe($expect['flagged'], implode(',', $result->categories));
            }
            foreach ($expect['categories_include'] ?? [] as $category) {
                expect($result->categories)->toContain($category);
            }
            if (isset($expect['refuse'])) {
                expect($result->refuse)->toBe($expect['refuse']);
            }
            break;

        case 'priority':
            $result = app(PriorityDetector::class)->detect($input['text']);
            expect($result['priority'])->toBe($expect['priority']);
            foreach ($expect['reasons_include'] ?? [] as $reason) {
                expect($result['reasons'])->toContain($reason);
            }
            break;

        case 'program':
            $this->seedExercises();
            [$tenant, $coach] = $this->proTenant();
            $payload = $this->actingAsIn($coach, $tenant)->postJson('/api/v1/ai/programs', $input)->assertCreated()->json('data.payload');
            expect($payload['days_per_week'])->toBe($expect['days_per_week'] ?? $payload['days_per_week']);
            if (isset($expect['goal'])) {
                expect($payload['goal'])->toBe($expect['goal']);
            }
            foreach ($payload['days'] as $day) {
                foreach ($day['workout']['blocks'] as $block) {
                    foreach ($block['exercises'] as $e) {
                        $exercise = Exercise::query()->find($e['exercise_id']);
                        expect($exercise)->not->toBeNull('library_only');
                        if (isset($expect['equipment_subset'])) {
                            expect(array_diff($exercise->equipment ?? [], $expect['equipment_subset']))->toBe([]);
                        }
                    }
                }
            }
            break;

        case 'meal_plan':
            $this->seedFoods();
            [$tenant, $coach] = $this->proTenant();
            if (isset($input['client_gender'])) {
                $input['client_id'] = $this->clientWithProfile($tenant, $coach, ['gender' => $input['client_gender']])->id;
                unset($input['client_gender']);
            }
            $response = $this->actingAsIn($coach, $tenant)->postJson('/api/v1/ai/meal-plans', $input)->assertCreated();
            $payload = $response->json('data.payload');
            $targets = $payload['targets'];
            foreach ($payload['days'] as $day) {
                $totals = ['kcal' => 0.0, 'protein_g' => 0.0, 'carbs_g' => 0.0, 'fat_g' => 0.0];
                foreach ($day['meals'] as $meal) {
                    if (isset($expect['slots'])) {
                        expect($expect['slots'])->toContain($meal['slot']);
                    }
                    foreach ($meal['items'] as $item) {
                        $food = Food::withoutGlobalScopes()->findOrFail($item['food_id']);
                        foreach (array_keys($totals) as $m) {
                            $totals[$m] += $food->{$m} * $item['quantity'] / 100;
                        }
                        foreach ($expect['diet_tags'] ?? [] as $tag) {
                            expect($food->diet_tags)->toContain($tag);
                        }
                        expect(array_intersect($food->allergens ?? [], $expect['excluded_allergens'] ?? []))->toBe([]);
                    }
                }
                foreach ($totals as $m => $v) {
                    expect(abs($v - $targets[$m]) / $targets[$m])->toBeLessThanOrEqual($expect['within_tolerance'], $m);
                }
            }
            if (isset($expect['language'])) {
                expect($payload['language'])->toBe($expect['language']);
            }
            if (isset($expect['kcal_target_min'])) {
                expect($targets['kcal'])->toBeGreaterThanOrEqual($expect['kcal_target_min']);
            }
            foreach ($expect['flags_include'] ?? [] as $flag) {
                expect(collect($response->json('data.flags'))->pluck('type'))->toContain($flag);
            }
            break;

        case 'business_ask':
            [$tenant, $coach] = $this->proTenant();
            $this->actingAsIn($coach, $tenant)->postJson('/api/v1/ai/ask', $input)->assertOk()->assertJsonPath('data.tool', $expect['tool']);
            break;

        case 'content':
            [$tenant, $coach] = $this->proTenant();
            $payload = $this->actingAsIn($coach, $tenant)->postJson('/api/v1/ai/content', $input)->assertCreated()->json('data.payload');
            expect($payload['language'])->toBe($expect['language'])->and($payload['body'])->not->toBe('');
            break;

        case 'photo_food':
            $this->seedFoods();
            [$tenant, $coach] = $this->proTenant();
            $client = $this->addClient($tenant, $coach);
            $candidates = $this->actingAsIn($client, $tenant)
                ->post('/api/v1/me/food-logs/photo', ['photo' => UploadedFile::fake()->image('plate.jpg', 1200, 900)], ['Accept' => 'application/json'])
                ->assertOk()->json('data.candidates');
            expect(count($candidates))->toBeGreaterThanOrEqual($expect['candidates_min'])
                ->and(count(array_filter($candidates, fn ($c) => $c['food'] !== null)))->toBeGreaterThanOrEqual($expect['mapped_min']);
            break;

        default:
            $this->fail("Unknown fixture feature {$fixture['feature']}");
    }
})->with('ai_fixtures');
