<?php

namespace App\Domain\Integrations\Data;

use Carbon\CarbonImmutable;

final class BusyBlock
{
    public function __construct(
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly string $source,
    ) {}

    /** @return array{starts_at: string, ends_at: string, source: string} */
    public function toArray(): array
    {
        return ['starts_at' => $this->start->toIso8601String(), 'ends_at' => $this->end->toIso8601String(), 'source' => $this->source];
    }
}
