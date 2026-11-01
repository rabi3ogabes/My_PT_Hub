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
 * OpenAI chat completions with `response_format: json_schema` (strict) for
 * structured output, image_url data URLs for vision, Whisper for speech and
 * the embeddings endpoint.
 */
final class OpenAiProvider implements AiProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly SchemaValidator $schemas,
    ) {}

    public function name(): string
    {
        return 'openai';
    }

    public function complete(AiPrompt $prompt): AiResponse
    {
        return $this->parse($this->post('/chat/completions', $this->body($prompt)), $prompt, false);
    }

    public function stream(AiPrompt $prompt, callable $onDelta): AiResponse
    {
        $body = $this->body($prompt) + ['stream' => true, 'stream_options' => ['include_usage' => true]];
        $response = $this->post('/chat/completions', $body, stream: true);

        $text = '';
        $tokensIn = 0;
        $tokensOut = 0;
        $model = $body['model'];
        $handle = function (string $line) use (&$text, &$tokensIn, &$tokensOut, &$model, $onDelta): void {
            $line = trim($line);
            if (! str_starts_with($line, 'data:') || trim(substr($line, 5)) === '[DONE]') {
                return;
            }
            $event = json_decode(trim(substr($line, 5)), true);
            if (! is_array($event)) {
                return;
            }
            $model = $event['model'] ?? $model;
            $delta = $event['choices'][0]['delta']['content'] ?? null;
            if (is_string($delta) && $delta !== '') {
                $text .= $delta;
                $onDelta($delta);
            }
            if (isset($event['usage'])) {
                $tokensIn = (int) ($event['usage']['prompt_tokens'] ?? 0);
                $tokensOut = (int) ($event['usage']['completion_tokens'] ?? 0);
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

        return new AiResponse($text, null, $tokensIn, $tokensOut, $model, $this->name(), 'stop');
    }

    public function structured(AiPrompt $prompt, array $schema, string $name = 'result'): AiResponse
    {
        return $this->parse($this->post('/chat/completions', $this->body($prompt, $schema, $name)), $prompt, true);
    }

    public function vision(AiPrompt $prompt, array $images, ?array $schema = null, string $name = 'result'): AiResponse
    {
        $body = $this->body($prompt, $schema, $name);
        // messages[0] is the system message; the first user turn carries the images.
        $first = $body['messages'][1];
        $parts = [['type' => 'text', 'text' => (string) $first['content']]];
        foreach ($images as $img) {
            $parts[] = ['type' => 'image_url', 'image_url' => ['url' => "data:{$img['media_type']};base64,{$img['data']}"]];
        }
        $body['messages'][1] = ['role' => 'user', 'content' => $parts];

        return $this->parse($this->post('/chat/completions', $body), $prompt, $schema !== null);
    }

    public function transcribe(string $path, ?string $language = null): AiResponse
    {
        $this->ensureKey();
        try {
            $response = $this->client()->attach('file', (string) file_get_contents($path), basename($path))
                ->post(config('ai.openai_url').'/audio/transcriptions', array_filter([
                    'model' => config('ai.openai_transcribe_model'),
                    'language' => $language,
                ]));
        } catch (ConnectionException $e) {
            throw new AiProviderException('OpenAI is unreachable: '.$e->getMessage());
        }
        $this->guard($response);

        return new AiResponse((string) $response->json('text'), null, 0, 0, (string) config('ai.openai_transcribe_model'), $this->name());
    }

    public function embed(array $texts): array
    {
        $response = $this->post('/embeddings', ['model' => config('ai.openai_embedding_model'), 'input' => array_values($texts)]);

        return array_map(fn (array $row) => array_map('floatval', $row['embedding']), $response->json('data') ?? []);
    }

    /**
     * @param  array<string, mixed>|null  $schema
     * @return array<string, mixed>
     */
    public function body(AiPrompt $prompt, ?array $schema = null, string $name = 'result'): array
    {
        $body = [
            'model' => $prompt->model ?? $this->model,
            'max_completion_tokens' => $prompt->maxTokens,
            'messages' => [
                ['role' => 'system', 'content' => $prompt->system],
                ...array_map(fn (array $m) => ['role' => $m['role'], 'content' => $m['content']], $prompt->messages),
            ],
        ];

        if ($schema !== null) {
            $body['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => ['name' => $name, 'strict' => true, 'schema' => $this->schemas->forProvider($schema)],
            ];
        }

        return $body;
    }

    /** @param array<string, mixed> $body */
    private function post(string $path, array $body, bool $stream = false): Response
    {
        $this->ensureKey();
        try {
            $request = $this->client();
            $response = ($stream ? $request->withOptions(['stream' => true]) : $request)->post(config('ai.openai_url').$path, $body);
        } catch (ConnectionException $e) {
            throw new AiProviderException('OpenAI is unreachable: '.$e->getMessage());
        }
        $this->guard($response);

        return $response;
    }

    private function client(): PendingRequest
    {
        return Http::withToken((string) $this->apiKey)->acceptJson()->timeout((int) config('ai.timeout_seconds', 120))
            ->retry(2, 500, fn ($e) => $e instanceof ConnectionException || ($e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503], true)), throw: false);
    }

    private function ensureKey(): void
    {
        if (! $this->apiKey) {
            throw new AiProviderException('OPENAI_API_KEY is not configured.');
        }
    }

    private function guard(Response $response): void
    {
        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new AiProviderException("OpenAI HTTP {$response->status()}: ".mb_substr((string) $message, 0, 300));
        }
    }

    private function parse(Response $response, AiPrompt $prompt, bool $structured): AiResponse
    {
        $json = $response->json() ?? [];
        $choice = $json['choices'][0] ?? [];
        $tokensIn = (int) ($json['usage']['prompt_tokens'] ?? 0);
        $tokensOut = (int) ($json['usage']['completion_tokens'] ?? 0);
        $model = (string) ($json['model'] ?? $prompt->model ?? $this->model);
        $text = (string) ($choice['message']['content'] ?? '');

        if (! empty($choice['message']['refusal'])) {
            throw new AiProviderException('The model declined this request.', 'refused', $tokensIn, $tokensOut);
        }

        $data = null;
        if ($structured) {
            $data = AnthropicProvider::extractJson($text);
            if ($data === null) {
                throw new AiProviderException('The model did not return structured output.', 'invalid', $tokensIn, $tokensOut);
            }
        }

        return new AiResponse($text, $data, $tokensIn, $tokensOut, $model, $this->name(), $choice['finish_reason'] ?? null);
    }
}
