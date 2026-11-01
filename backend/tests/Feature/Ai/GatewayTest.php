<?php

use App\Domain\Ai\Exceptions\AiProviderException;
use App\Domain\Ai\Models\AiRequest;
use App\Domain\Ai\Models\AiSetting;
use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\UsageMeter;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Ai\Support\AiFixtures;

uses(AiFixtures::class);

beforeEach(function () {
    $this->seedExercises();
});

function askContent($test): \Illuminate\Testing\TestResponse
{
    return $test->postJson('/api/v1/ai/content', ['type' => 'instagram_caption', 'topic' => 'Summer shred', 'language' => 'en']);
}

it('meters every request into ai_requests and usage_meters', function () {
    [$tenant, $coach] = $this->proTenant();

    $this->actingAsIn($coach, $tenant)->postJson('/api/v1/ai/programs', ['prompt' => '3 day full body'])->assertCreated();

    $request = AiRequest::query()->where('tenant_id', $tenant->id)->sole();
    expect($request->feature)->toBe('program')
        ->and($request->provider)->toBe('fake')
        ->and($request->status)->toBe('ok')
        ->and($request->prompt_version)->toBe('v1')
        ->and($request->user_id)->toBe($coach->id)
        ->and($request->tokens_in)->toBeGreaterThan(100)
        ->and($request->tokens_out)->toBeGreaterThan(100);

    $period = now()->format('Y-m');
    expect(UsageMeter::query()->where('tenant_id', $tenant->id)->where('metric', 'ai_requests')->where('period', $period)->value('value'))->toBe(1)
        ->and(UsageMeter::query()->where('tenant_id', $tenant->id)->where('metric', 'ai_tokens')->value('value'))->toBe($request->tokens_in + $request->tokens_out);
});

it('records cost in micros for priced models', function () {
    [$tenant, $coach] = $this->proTenant();
    $this->fake()->push('content', fn () => ['title' => null, 'body' => 'Train smart.', 'hashtags' => [], 'language' => 'en']);
    config(['ai.pricing.fake' => ['in' => 2.0, 'out' => 10.0]]);

    $this->actingAsIn($coach, $tenant);
    askContent($this)->assertCreated();

    $r = AiRequest::query()->sole();
    expect($r->cost_micros)->toBe($r->tokens_in * 2 + $r->tokens_out * 10)
        ->and(UsageMeter::query()->where('metric', 'ai_requests')->value('cost_micros'))->toBe($r->cost_micros);
});

it('blocks the free plan from the coach copilot once its monthly allowance is used', function () {
    [$tenant, $coach] = $this->createCoachTenant(); // free plan: 20 requests/month, no ai_copilot
    $this->actingAsIn($coach, $tenant);

    askContent($this)->assertCreated(); // within the allowance
    UsageMeter::query()->where('tenant_id', $tenant->id)->where('metric', 'ai_requests')->update(['value' => 20]);

    expectApiError(askContent($this), 402, 'plan_limit_reached');
    expect(AiRequest::query()->count())->toBe(1);
});

it('blocks coach features entirely when the plan has neither the feature nor an allowance', function () {
    SaasPlan::query()->where('code', 'free')->update(['limits' => ['clients' => 3, 'coaches' => 1, 'ai_requests_month' => 0]]);
    Cache::flush();
    [$tenant, $coach] = $this->createCoachTenant();

    $this->actingAsIn($coach, $tenant);
    $response = askContent($this);
    expectApiError($response, 402, 'plan_limit_reached');
    expect($response->json('errors.0.message'))->toBe(__('ai.errors.plan_required'));
});

it('applies the monthly cap of paid plans and the fair-use cost cap', function () {
    [$tenant, $coach] = $this->proTenant();
    $this->actingAsIn($coach, $tenant);
    UsageMeter::query()->create(['tenant_id' => $tenant->id, 'metric' => 'ai_requests', 'period' => now()->format('Y-m'), 'value' => 2000, 'cost_micros' => 0]);
    expectApiError(askContent($this), 402, 'plan_limit_reached');

    UsageMeter::query()->update(['value' => 5, 'cost_micros' => 60_000_000]);
    config(['services.ai.monthly_cap_micros' => 50_000_000]);
    $response = askContent($this);
    expectApiError($response, 402, 'plan_limit_reached');
    expect($response->json('errors.0.message'))->toBe(__('ai.errors.cost_cap'));
});

it('honours tenant opt-out and per-feature disable', function () {
    [$tenant, $owner] = $this->proTenant();
    $coach = $this->addStaff($tenant, 'coach');

    $this->inTenant($tenant, fn () => AiSetting::query()->create(['user_id' => $owner->id, 'disabled_features' => ['content']]));
    $this->actingAsIn($coach, $tenant);
    expectApiError(askContent($this), 403, 'ai_feature_disabled');

    $this->inTenant($tenant, fn () => AiSetting::query()->where('user_id', $owner->id)->update(['opt_out' => true]));
    expectApiError($this->postJson('/api/v1/ai/programs', ['prompt' => '3 day']), 403, 'ai_disabled');
    expect(AiRequest::query()->count())->toBe(0);
});

it('degrades gracefully when the provider fails and logs the error', function () {
    [$tenant, $coach] = $this->proTenant();
    $this->fake()->push('content', new AiProviderException('upstream timeout'));

    $this->actingAsIn($coach, $tenant);
    expectApiError(askContent($this), 503, 'ai_unavailable');
    expect(AiRequest::query()->sole()->status)->toBe('error')
        ->and(UsageMeter::query()->where('metric', 'ai_requests')->value('value'))->toBe(1);
});

it('maps model refusals to ai_refused', function () {
    [$tenant, $coach] = $this->proTenant();
    $this->fake()->push('content', new AiProviderException('declined', 'refused'));

    $this->actingAsIn($coach, $tenant);
    expectApiError(askContent($this), 422, 'ai_refused');
    expect(AiRequest::query()->sole()->status)->toBe('refused');
});

it('uses the Anthropic driver end-to-end when configured', function () {
    config(['services.ai.driver' => 'anthropic', 'services.ai.anthropic_key' => 'sk-ant-x']);
    \Illuminate\Support\Facades\Http::fake(['api.anthropic.com/*' => \Illuminate\Support\Facades\Http::response([
        'model' => 'claude-sonnet-5-5', 'stop_reason' => 'tool_use', 'usage' => ['input_tokens' => 400, 'output_tokens' => 80],
        'content' => [['type' => 'tool_use', 'name' => 'content', 'input' => ['title' => null, 'body' => 'Stronger every week.', 'hashtags' => ['#fit'], 'language' => 'en']]],
    ])]);
    [$tenant, $coach] = $this->proTenant();

    $this->actingAsIn($coach, $tenant);
    askContent($this)->assertCreated()->assertJsonPath('data.payload.body', 'Stronger every week.');

    $r = AiRequest::query()->sole();
    expect($r->provider)->toBe('anthropic')->and($r->model)->toBe('claude-sonnet-5-5')
        ->and($r->tokens_in)->toBe(400)->and($r->cost_micros)->toBe(400 * 2 + 80 * 10);
});
