<?php

namespace App\Domain\Ai\Data;

/** What AiGateway returns to a feature after gating, metering and validation. */
final class AiResult
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public readonly string $text,
        public readonly ?array $data,
        public readonly ?string $requestId,
        public readonly int $attempts,
        public readonly int $tokensIn,
        public readonly int $tokensOut,
        public readonly int $costMicros,
        public readonly string $model,
        public readonly string $promptVersion,
    ) {}
}
