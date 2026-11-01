<?php

namespace App\Domain\Ai\Exceptions;

use RuntimeException;

/** A provider call failed (network, HTTP error, refusal, unsupported capability). */
class AiProviderException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $reason = 'error', // error|refused|unsupported|invalid
        public readonly int $tokensIn = 0,
        public readonly int $tokensOut = 0,
    ) {
        parent::__construct($message);
    }

    public static function unsupported(string $provider, string $capability): self
    {
        return new self("{$provider} does not support {$capability}.", 'unsupported');
    }
}
