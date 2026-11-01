<?php

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Contracts\AiProvider;
use App\Domain\Ai\Data\AiPrompt;
use App\Domain\Ai\Data\AiResponse;
use App\Domain\Ai\Exceptions\AiProviderException;
use App\Domain\Ai\Support\SchemaValidator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Claude via the Anthropic Messages API (POST /v1/messages, raw HTTP).
 *
 * Structured output uses a single strict client tool: the schema is the
 * tool's input_schema and Claude is instructed to answer by calling it
 * (tool_choice stays "auto" — forced tool choice is rejected by current
 * models). The system prompt is sent as a cacheable block so repeated
 * feature calls reuse the prefix.
 */
final class AnthropicProvider implements AiProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly string $visionModel,
        private readonly SchemaValidator $schemas,
        private readonly ?OpenAiProvider $transcriber = null,
    ) {}

    public function name(): string
    {
        return 'anthropic';
    }

    public function complete(AiPrompt $prompt): AiResponse
    {
        return $this->parse($this->send($this->body($prompt)), $prompt, null);
    }

    public function stream(AiPrompt $prompt, callable $onDelta): AiResponse
    {
        $body = $this->body($prompt) + ['stream' => true];
        $response = $this->send($body, stream: true);

        $text = '';
        $tokensIn = 0;
        $tokensOut = 0;
        $model = $body['model'];
        $stopReason = null;
        $handle = function (string $line) use (&$text, &$tokensIn, &$tokensOut, &$model, &$stopReason, $onDelta): void {
            $line = trim($line);
            if (! str_starts_with($line, 'data:')) {
                return;
            }
            $event = json_decode(trim(substr($line, 5)), true);
            if (! is_array($event)) {
                return;
            }
            switch ($event['type'] ?? null) {
                case 'message_start':
                    $tokensIn = $this->inputTokens($event['message']['usage'] ?? []);
                    $model = $event['message']['model'] ?? $model;
                    break;
                case 'content_block_delta':
                    if (($event['delta']['type'] ?? null) === 'text_delta') {
                        $delta = (string) $event['delta']['text'];
                        $text .= $delta;
                        $onDelta($delta);
                    }
                    break;
                case 'message_delta':
                    $tokensOut = (int) ($event['usage']['output_tokens'] ?? $tokensOut);
                    $stopReason = $event['delta']['stop_reason'] ?? $stopReason;
                    break;
                case 'error':
                    throw new AiProviderException('Anthropic stream error: '.($event['error']['message'] ?? 'unknown'));
            }
        };

        $stream = $response->toPsrResponse()->getBody();
        $buffer = '';
        while (true) {
            if (! $stream->eof()) {
                $buffer .= $stream->read(8192);
            }
            while (($pos = strpos($buffer, "\n")) !== false) {
                $handle(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);
            }
            if ($stream->eof()) {
                if ($buffer !== '') {
                    $handle($buffer);
                }
                break;
            }
        }

        if ($stopReason === 'refusal') {
            throw new AiProviderException('The model declined this request.', 'refused', $tokensIn, $tokensOut);
        }

        return new AiResponse($text, null, $tokensIn, $tokensOut, $model, $this->name(), $stopReason);
    }

    public function structured(AiPrompt $prompt, array $schema, string $name = 'result'): AiResponse
    {
        return $this->parse($this->send($this->body($prompt, $schema, $name)), $prompt, $name);
    }

    public function vision(AiPrompt $prompt, array $images, ?array $schema = null, string $name = 'result'): AiResponse
    {
        $prompt = $prompt->withModel($prompt->model ?? $this->visionModel);
        $body = $this->body($prompt, $schema, $name);

        // Images go first in the opening user turn, before its text.
        $first = $body['messages'][0];
        $blocks = array_map(fn (array $img) => [
            'type' => 'image',
            'source' => ['type' => 'base64', 'media_type' => $img['media_type'], 'data' => $img['data']],
        ], $images);
        $blocks[] = ['type' => 'text', 'text' => is_string($first['content']) ? $first['content'] : ''];
        $body['messages'][0] = ['role' => 'user', 'content' => $blocks];

        return $this->parse($this->send($body), $prompt, $schema ? $name : null);
    }

    public function transcribe(string $path, ?string $language = null): AiResponse
    {
        if ($this->transcriber === null) {
            throw AiProviderException::unsupported('anthropic', 'audio transcription');
        }

        return $this->transcriber->transcribe($path, $language);
    }

    public function embed(array $texts): array
    {
        throw AiProviderException::unsupported('anthropic', 'embeddings');
    }

    /**
     * @param  array<string, mixed>|null  $schema
     * @return array<string, mixed>
     */
    public function body(AiPrompt $prompt, ?array $schema = null, string $name = 'result'): array
    {
        $system = $prompt->system;
        if ($schema !== null) {
            $system .= "\n\nRespond only by calling the `{$name}` tool exactly once with the complete result. Do not answer in plain text.";
        }

        $body = [
            'model' => $prompt->model ?? $this->model,
            'max_tokens' => $prompt->maxTokens,
            'system' => [['type' => 'text', 'text' => $system, 'cache_control' => ['type' => 'ephemeral']]],
            'messages' => array_map(fn (array $m) => ['role' => $m['role'], 'content' => $m['content']], $prompt->messages),
        ];

        if ($effort = config('ai.anthropic_effort')) {
            $body['output_config'] = ['effort' => $effort];
        }

        if ($schema !== null) {
            $body['tools'] = [[
                'name' => $name,
                'description' => "Return the {$prompt->feature} result as structured JSON.",
                'input_schema' => $this->schemas->forProvider($schema),
                'strict' => true,
            ]];
            $body['tool_choice'] = ['type' => 'auto'];
        }

        return $body;
    }

    /** @param array<string, mixed> $body */
    private function send(array $body, bool $stream = false): Response
    {
        if (! $this->apiKey) {
            throw new AiProviderException('ANTHROPIC_API_KEY is not configured.');
        }

        try {
            $response = $this->client($stream)->post(config('ai.anthropic_url'), $body);
        } catch (ConnectionException $e) {
            throw new AiProviderException('Anthropic is unreachable: '.$e->getMessage());
        }

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new AiProviderException("Anthropic HTTP {$response->status()}: ".mb_substr((string) $message, 0, 300));
        }

        return $response;
    }

    private function client(bool $stream): PendingRequest
    {
        $request = Http::withHeaders([
            'x-api-key' => (string) $this->apiKey,
            'anthropic-version' => (string) config('ai.anthropic_version'),
            'content-type' => 'application/json',
        ])->acceptJson()->timeout((int) config('ai.timeout_seconds', 120))
            ->retry(2, 500, fn ($e) => $e instanceof ConnectionException || ($e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503, 529], true)), throw: false);

        return $stream ? $request->withOptions(['stream' => true]) : $request;
    }

    private function parse(Response $response, AiPrompt $prompt, ?string $toolName): AiResponse
    {
        $json = $response->json() ?? [];
        $usage = $json['usage'] ?? [];
        $tokensIn = $this->inputTokens($usage);
        $tokensOut = (int) ($usage['output_tokens'] ?? 0);
        $stopReason = $json['stop_reason'] ?? null;
        $model = (string) ($json['model'] ?? $prompt->model ?? $this->model);

        if ($stopReason === 'refusal') {
            throw new AiProviderException('The model declined this request.', 'refused', $tokensIn, $tokensOut);
        }

        $text = '';
        $data = null;
        foreach ($json['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'text') {
                $text .= $block['text'];
            } elseif (($block['type'] ?? null) === 'tool_use' && ($toolName === null || ($block['name'] ?? null) === $toolName)) {
                $data = is_array($block['input'] ?? null) ? $block['input'] : null;
            }
        }

        if ($toolName !== null && $data === null) {
            $data = self::extractJson($text);
            if ($data === null) {
                throw new AiProviderException('The model did not return structured output'.($stopReason === 'max_tokens' ? ' (max_tokens reached).' : '.'), 'invalid', $tokensIn, $tokensOut);
            }
        }

        return new AiResponse($text, $data, $tokensIn, $tokensOut, $model, $this->name(), $stopReason);
    }

    /** @param array<string, mixed> $usage */
    private function inputTokens(array $usage): int
    {
        return (int) ($usage['input_tokens'] ?? 0)
            + (int) ($usage['cache_creation_input_tokens'] ?? 0)
            + (int) ($usage['cache_read_input_tokens'] ?? 0);
    }

    /** @return array<string, mixed>|null */
    public static function extractJson(string $text): ?array
    {
        $text = trim($text);
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $text, $m)) {
            $text = $m[1];
        } elseif (($start = strpos($text, '{')) !== false && ($end = strrpos($text, '}')) !== false) {
            $text = substr($text, $start, $end - $start + 1);
        }
        $decoded = json_decode($text, true);

        return is_array($decoded) && ! array_is_list($decoded) ? $decoded : null;
    }
}
