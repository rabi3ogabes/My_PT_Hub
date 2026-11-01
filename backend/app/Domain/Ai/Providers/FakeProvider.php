<?php

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Contracts\AiProvider;
use App\Domain\Ai\Data\AiPrompt;
use App\Domain\Ai\Data\AiResponse;
use App\Domain\Ai\Providers\Fake\FakeResponder;
use Closure;
use Throwable;

/**
 * Deterministic driver (AI_DRIVER=fake): used by the test-suite and to demo
 * the product without provider keys. Responses are built from the prompt's
 * structured context (real library exercises, real foods, real client
 * numbers), so outputs are realistic and pass the same validation as real
 * model output. Tests can script responses per feature with push().
 */
final class FakeProvider implements AiProvider
{
    /** @var array<string, list<mixed>> */
    private array $queue = [];

    /** @var list<array{method: string, feature: string, prompt: AiPrompt, images: int}> */
    public array $calls = [];

    public function __construct(private readonly FakeResponder $responder = new FakeResponder) {}

    public function name(): string
    {
        return 'fake';
    }

    /**
     * Queue the next response for a feature: an array (structured data), a
     * string (text), a Throwable (thrown) or a Closure(AiPrompt): array|string.
     */
    public function push(string $feature, mixed $response): self
    {
        $this->queue[$feature][] = $response;

        return $this;
    }

    public function reset(): void
    {
        $this->queue = [];
        $this->calls = [];
    }

    /** @return list<array{method: string, feature: string, prompt: AiPrompt, images: int}> */
    public function callsFor(string $feature): array
    {
        return array_values(array_filter($this->calls, fn (array $c) => $c['feature'] === $feature));
    }

    public function complete(AiPrompt $prompt): AiResponse
    {
        $this->record('complete', $prompt);
        $out = $this->next($prompt) ?? $this->responder->text($prompt->feature, $prompt->context);

        return $this->response($prompt, is_string($out) ? $out : (string) json_encode($out), null);
    }

    public function stream(AiPrompt $prompt, callable $onDelta): AiResponse
    {
        $this->record('stream', $prompt);
        $out = $this->next($prompt) ?? $this->responder->text($prompt->feature, $prompt->context);
        $text = is_string($out) ? $out : (string) json_encode($out);
        foreach (preg_split('/(?<=\s)/u', $text) ?: [] as $piece) {
            if ($piece !== '') {
                $onDelta($piece);
            }
        }

        return $this->response($prompt, $text, null);
    }

    public function structured(AiPrompt $prompt, array $schema, string $name = 'result'): AiResponse
    {
        $this->record('structured', $prompt);
        $out = $this->next($prompt) ?? $this->responder->structured($prompt->feature, $prompt->context);
        $data = is_array($out) ? $out : (AnthropicProvider::extractJson((string) $out) ?? ['text' => (string) $out]);

        return $this->response($prompt, '', $data);
    }

    public function vision(AiPrompt $prompt, array $images, ?array $schema = null, string $name = 'result'): AiResponse
    {
        $this->record('vision', $prompt, count($images));
        $out = $this->next($prompt) ?? ($schema !== null
            ? $this->responder->structured($prompt->feature, $prompt->context + ['image_count' => count($images)])
            : $this->responder->text($prompt->feature, $prompt->context));

        return is_array($out) ? $this->response($prompt, '', $out) : $this->response($prompt, (string) $out, null);
    }

    public function transcribe(string $path, ?string $language = null): AiResponse
    {
        $text = $this->responder->transcript($language ?? 'en');

        return new AiResponse($text, null, 0, (int) ceil(mb_strlen($text) / 4), 'fake', 'fake');
    }

    public function embed(array $texts): array
    {
        return array_map(function (string $text) {
            $vector = array_fill(0, 16, 0.0);
            foreach (preg_split('//u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $i => $char) {
                $vector[(mb_ord($char) + $i) % 16] += 1.0;
            }
            $norm = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vector))) ?: 1.0;

            return array_map(fn ($v) => $v / $norm, $vector);
        }, array_values($texts));
    }

    private function record(string $method, AiPrompt $prompt, int $images = 0): void
    {
        $this->calls[] = ['method' => $method, 'feature' => $prompt->feature, 'prompt' => $prompt, 'images' => $images];
    }

    private function next(AiPrompt $prompt): mixed
    {
        if (empty($this->queue[$prompt->feature])) {
            return null;
        }
        $item = array_shift($this->queue[$prompt->feature]);
        if ($item instanceof Throwable) {
            throw $item;
        }

        return $item instanceof Closure ? $item($prompt) : $item;
    }

    /** @param array<string, mixed>|null $data */
    private function response(AiPrompt $prompt, string $text, ?array $data): AiResponse
    {
        $input = mb_strlen($prompt->system) + array_sum(array_map(fn ($m) => mb_strlen(is_string($m['content']) ? $m['content'] : ''), $prompt->messages));
        $output = $data !== null ? mb_strlen((string) json_encode($data, JSON_UNESCAPED_UNICODE)) : mb_strlen($text);

        return new AiResponse($text, $data, (int) ceil($input / 4), (int) ceil($output / 4), 'fake', 'fake', $data !== null ? 'tool_use' : 'end_turn');
    }
}
