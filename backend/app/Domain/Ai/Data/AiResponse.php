<?php

namespace App\Domain\Ai\Data;

final class AiResponse
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public readonly string $text,
        public readonly ?array $data,
        public readonly int $tokensIn,
        public readonly int $tokensOut,
        public readonly string $model,
        public readonly string $provider,
        public readonly ?string $stopReason = null,
    ) {}
}
