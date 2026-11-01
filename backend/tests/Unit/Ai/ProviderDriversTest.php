<?php

use App\Domain\Ai\Data\AiPrompt;
use App\Domain\Ai\Exceptions\AiProviderException;
use App\Domain\Ai\Providers\AnthropicProvider;
use App\Domain\Ai\Providers\OpenAiProvider;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Ai\Support\SchemaValidator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

function anthropic(): AnthropicProvider
{
    return new AnthropicProvider('sk-ant-test', 'claude-sonnet-5-5', 'claude-sonnet-5-5', new SchemaValidator);
}

function samplePrompt(string $feature = 'reply_draft'): AiPrompt
{
    return new AiPrompt($feature, 'You are a coach assistant.', [['role' => 'user', 'content' => 'Draft a reply to Omar.']], null, 1024);
}

it('sends a Messages API request with strict tool-use structured output', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-sonnet-5-5',
            'content' => [['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'reply_draft', 'input' => ['reply' => 'Great work Omar!', 'language' => 'en']]],
            'stop_reason' => 'tool_use',
            'usage' => ['input_tokens' => 120, 'output_tokens' => 30, 'cache_read_input_tokens' => 10],
        ]),
    ]);

    $response = anthropic()->structured(samplePrompt(), Schemas::replyDraft(), 'reply_draft');

    expect($response->data)->toBe(['reply' => 'Great work Omar!', 'language' => 'en'])
        ->and($response->tokensIn)->toBe(130)
        ->and($response->tokensOut)->toBe(30)
        ->and($response->provider)->toBe('anthropic');

    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $request->url() === 'https://api.anthropic.com/v1/messages'
            && $request->hasHeader('x-api-key', 'sk-ant-test')
            && $request->hasHeader('anthropic-version', config('ai.anthropic_version'))
            && $body['model'] === 'claude-sonnet-5-5'
            && $body['max_tokens'] === 1024
            && $body['system'][0]['cache_control'] === ['type' => 'ephemeral']
            && str_contains($body['system'][0]['text'], 'reply_draft')
            && $body['tools'][0]['name'] === 'reply_draft'
            && $body['tools'][0]['strict'] === true
            && $body['tools'][0]['input_schema']['additionalProperties'] === false
            && ! isset(((array) $body['tools'][0]['input_schema']['properties'])['reply']['maxLength'])
            && $body['tool_choice'] === ['type' => 'auto']
            && ! array_key_exists('temperature', $body)
            && $body['messages'][0]['role'] === 'user';
    });
});

it('uses the configured default model', function () {
    expect(config('services.ai.anthropic_model'))->toBe('claude-sonnet-5-5');
});

it('sends images as base64 blocks before the text for vision', function () {
    Http::fake(['api.anthropic.com/*' => Http::response([
        'model' => 'claude-sonnet-5-5', 'stop_reason' => 'tool_use', 'usage' => ['input_tokens' => 900, 'output_tokens' => 50],
        'content' => [['type' => 'tool_use', 'name' => 'photo_food', 'input' => ['items' => [], 'notes' => null]]],
    ])]);

    anthropic()->vision(samplePrompt('photo_food'), [['media_type' => 'image/jpeg', 'data' => base64_encode('img')]], Schemas::photoFood(), 'photo_food');

    Http::assertSent(function (Request $request) {
        $content = $request->data()['messages'][0]['content'];

        return $content[0]['type'] === 'image'
            && $content[0]['source'] === ['type' => 'base64', 'media_type' => 'image/jpeg', 'data' => base64_encode('img')]
            && $content[1]['type'] === 'text';
    });
});

it('falls back to JSON in text and reports refusals and HTTP errors', function () {
    Http::fake(['api.anthropic.com/*' => Http::sequence()
        ->push(['model' => 'claude-sonnet-5-5', 'stop_reason' => 'end_turn', 'usage' => ['input_tokens' => 5, 'output_tokens' => 5], 'content' => [['type' => 'text', 'text' => "```json\n{\"reply\":\"Hi\",\"language\":\"en\"}\n```"]]])
        ->push(['model' => 'claude-sonnet-5-5', 'stop_reason' => 'refusal', 'usage' => ['input_tokens' => 5, 'output_tokens' => 0], 'content' => []])
        ->push(['type' => 'error', 'error' => ['type' => 'invalid_request_error', 'message' => 'bad request']], 400),
    ]);

    expect(anthropic()->structured(samplePrompt(), Schemas::replyDraft(), 'reply_draft')->data)->toBe(['reply' => 'Hi', 'language' => 'en']);

    try {
        anthropic()->structured(samplePrompt(), Schemas::replyDraft(), 'reply_draft');
        $this->fail('Expected refusal');
    } catch (AiProviderException $e) {
        expect($e->reason)->toBe('refused');
    }

    expect(fn () => anthropic()->complete(samplePrompt()))->toThrow(AiProviderException::class, 'Anthropic HTTP 400');
});

it('parses server-sent events when streaming', function () {
    $sse = implode("\n", [
        'event: message_start',
        'data: {"type":"message_start","message":{"model":"claude-sonnet-5-5","usage":{"input_tokens":42,"output_tokens":1}}}',
        '',
        'event: content_block_delta',
        'data: {"type":"content_block_delta","index":0,"delta":{"type":"text_delta","text":"Hello "}}',
        '',
        'event: content_block_delta',
        'data: {"type":"content_block_delta","index":0,"delta":{"type":"text_delta","text":"Omar"}}',
        '',
        'event: message_delta',
        'data: {"type":"message_delta","delta":{"stop_reason":"end_turn"},"usage":{"output_tokens":7}}',
        '',
        'event: message_stop',
        'data: {"type":"message_stop"}',
        '',
    ]);
    Http::fake(['api.anthropic.com/*' => Http::response($sse, 200, ['Content-Type' => 'text/event-stream'])]);

    $deltas = [];
    $response = anthropic()->stream(samplePrompt('coach_chat'), function (string $d) use (&$deltas) {
        $deltas[] = $d;
    });

    expect($deltas)->toBe(['Hello ', 'Omar'])
        ->and($response->text)->toBe('Hello Omar')
        ->and($response->tokensIn)->toBe(42)
        ->and($response->tokensOut)->toBe(7);
    Http::assertSent(fn (Request $r) => $r->data()['stream'] === true);
});

it('refuses to call Anthropic without an API key', function () {
    $provider = new AnthropicProvider(null, 'claude-sonnet-5-5', 'claude-sonnet-5-5', new SchemaValidator);
    expect(fn () => $provider->complete(samplePrompt()))->toThrow(AiProviderException::class);
    expect(fn () => $provider->embed(['x']))->toThrow(AiProviderException::class);
});

it('sends OpenAI chat completions with a strict json_schema response format', function () {
    Http::fake(['api.openai.com/v1/chat/completions' => Http::response([
        'model' => 'gpt-4.1-mini',
        'choices' => [['message' => ['role' => 'assistant', 'content' => '{"reply":"Salut !","language":"fr"}'], 'finish_reason' => 'stop']],
        'usage' => ['prompt_tokens' => 80, 'completion_tokens' => 12],
    ])]);

    $provider = new OpenAiProvider('sk-test', 'gpt-4.1-mini', new SchemaValidator);
    $response = $provider->structured(samplePrompt(), Schemas::replyDraft(), 'reply_draft');

    expect($response->data)->toBe(['reply' => 'Salut !', 'language' => 'fr'])->and($response->tokensIn)->toBe(80);
    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $request->hasHeader('Authorization', 'Bearer sk-test')
            && $body['messages'][0] === ['role' => 'system', 'content' => 'You are a coach assistant.']
            && $body['response_format']['type'] === 'json_schema'
            && $body['response_format']['json_schema']['strict'] === true
            && $body['response_format']['json_schema']['schema']['required'] === ['reply', 'language'];
    });
});

it('calls OpenAI embeddings and vision', function () {
    Http::fake([
        'api.openai.com/v1/embeddings' => Http::response(['data' => [['embedding' => [0.1, 0.2]], ['embedding' => [0.3, 0.4]]]]),
        'api.openai.com/v1/chat/completions' => Http::response(['model' => 'gpt-4.1-mini', 'choices' => [['message' => ['content' => '{"items":[],"notes":null}']]], 'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1]]),
    ]);
    $provider = new OpenAiProvider('sk-test', 'gpt-4.1-mini', new SchemaValidator);

    expect($provider->embed(['a', 'b']))->toBe([[0.1, 0.2], [0.3, 0.4]]);
    $provider->vision(samplePrompt('photo_food'), [['media_type' => 'image/png', 'data' => 'AAA']], Schemas::photoFood(), 'photo_food');

    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/chat/completions')
        && $r->data()['messages'][1]['content'][1] === ['type' => 'image_url', 'image_url' => ['url' => 'data:image/png;base64,AAA']]);
});
