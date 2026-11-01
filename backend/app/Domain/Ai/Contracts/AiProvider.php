<?php

namespace App\Domain\Ai\Contracts;

use App\Domain\Ai\Data\AiPrompt;
use App\Domain\Ai\Data\AiResponse;

/**
 * A large-language-model driver. Implementations: Anthropic (default),
 * OpenAI and a deterministic Fake used by tests and key-less demos.
 * Drivers never meter, gate or validate — AiGateway does that.
 */
interface AiProvider
{
    public function name(): string;

    /** Free-form text completion. */
    public function complete(AiPrompt $prompt): AiResponse;

    /**
     * Streams text deltas to $onDelta(string $delta) and returns the full response.
     *
     * @param  callable(string): void  $onDelta
     */
    public function stream(AiPrompt $prompt, callable $onDelta): AiResponse;

    /**
     * JSON output conforming to a JSON schema. AiResponse::$data holds the decoded object.
     *
     * @param  array<string, mixed>  $schema
     */
    public function structured(AiPrompt $prompt, array $schema, string $name = 'result'): AiResponse;

    /**
     * Image understanding. Each image: ['media_type' => 'image/jpeg', 'data' => base64].
     * With a schema the response is structured, otherwise text.
     *
     * @param  list<array{media_type: string, data: string}>  $images
     * @param  array<string, mixed>|null  $schema
     */
    public function vision(AiPrompt $prompt, array $images, ?array $schema = null, string $name = 'result'): AiResponse;

    /** Speech to text for a local audio file. */
    public function transcribe(string $path, ?string $language = null): AiResponse;

    /**
     * @param  list<string>  $texts
     * @return list<list<float>>
     */
    public function embed(array $texts): array;
}
