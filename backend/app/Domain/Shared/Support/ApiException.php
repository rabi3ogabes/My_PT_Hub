<?php

namespace App\Domain\Shared\Support;

use RuntimeException;

/** A domain error that renders as a consistent API error envelope. */
class ApiException extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $fieldErrors
     */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 422,
        public readonly array $fieldErrors = [],
    ) {
        parent::__construct($message);
    }

    public static function forbidden(string $message = 'This action is not allowed.'): self
    {
        return new self('forbidden', $message, 403);
    }

    public static function limit(string $message): self
    {
        return new self('plan_limit_reached', $message, 402);
    }

    public static function conflict(string $code, string $message): self
    {
        return new self($code, $message, 409);
    }

    public static function invalid(string $code, string $message, array $fieldErrors = []): self
    {
        return new self($code, $message, 422, $fieldErrors);
    }
}
