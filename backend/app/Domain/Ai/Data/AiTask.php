<?php

namespace App\Domain\Ai\Data;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use Closure;

/**
 * One feature invocation handed to AiGateway.
 *
 * kind: text | structured | vision | stream.
 * postProcess(array $data): array{0: array, 1: list<string>} normalises a
 * structured output and returns domain validation errors (fed back to the
 * model on retry).
 */
final class AiTask
{
    /**
     * @param  array<string, mixed>  $variables  template variables
     * @param  array<string, mixed>  $context  structured data the prompt is grounded on
     * @param  array<string, mixed>|null  $schema
     * @param  list<array{media_type: string, data: string}>  $images
     * @param  list<array{role: string, content: string}>  $history  prior chat turns (text kinds)
     */
    public function __construct(
        public readonly string $feature,
        public readonly Tenant $tenant,
        public readonly ?User $user,
        public readonly string $kind = 'structured',
        public readonly array $variables = [],
        public readonly array $context = [],
        public readonly ?array $schema = null,
        public readonly array $images = [],
        public readonly ?Closure $postProcess = null,
        public readonly ?Closure $onDelta = null,
        public readonly array $history = [],
    ) {}
}
