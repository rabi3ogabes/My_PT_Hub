<?php

namespace App\Domain\Ai\Data;

/**
 * A provider-agnostic request: system prompt + conversation. `context` is the
 * structured data the prompt was rendered from (used for grounding and by the
 * Fake driver to build realistic outputs) and is never sent to a provider.
 */
final class AiPrompt
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly string $feature,
        public readonly string $system,
        public readonly array $messages,
        public readonly ?string $model = null,
        public readonly int $maxTokens = 4096,
        public readonly array $context = [],
        public readonly ?string $promptVersion = null,
    ) {}

    public function withMessage(string $role, string $content): self
    {
        return new self($this->feature, $this->system, [...$this->messages, ['role' => $role, 'content' => $content]], $this->model, $this->maxTokens, $this->context, $this->promptVersion);
    }

    public function withModel(?string $model): self
    {
        return new self($this->feature, $this->system, $this->messages, $model, $this->maxTokens, $this->context, $this->promptVersion);
    }

    /** Number of retry turns already appended (validation feedback). */
    public function attempt(): int
    {
        return intdiv(max(0, count($this->messages) - 1), 2);
    }
}
