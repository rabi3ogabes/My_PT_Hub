<?php

namespace App\Domain\Ai\Data;

/** Outcome of SafetyGuard screening. */
final class SafetyResult
{
    /**
     * @param  list<string>  $categories  eating_disorder|medical|injury|minor|extreme_deficit|parq|self_harm
     * @param  list<array{category: string, match: string}>  $matches
     */
    public function __construct(
        public readonly array $categories = [],
        public readonly bool $refuse = false,
        public readonly array $matches = [],
    ) {}

    public function flagged(): bool
    {
        return $this->categories !== [];
    }

    public function has(string $category): bool
    {
        return in_array($category, $this->categories, true);
    }

    public function merge(self $other): self
    {
        return new self(
            array_values(array_unique([...$this->categories, ...$other->categories])),
            $this->refuse || $other->refuse,
            [...$this->matches, ...$other->matches],
        );
    }

    /** @return list<array{type: string, severity: string, source: string}> */
    public function toFlags(): array
    {
        return array_map(fn (string $c) => [
            'type' => $c,
            'severity' => in_array($c, ['eating_disorder', 'medical', 'minor', 'extreme_deficit', 'self_harm'], true) ? 'high' : 'medium',
            'source' => 'safety_guard',
        ], $this->categories);
    }
}
