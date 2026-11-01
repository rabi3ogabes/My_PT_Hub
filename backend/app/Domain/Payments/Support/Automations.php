<?php

namespace App\Domain\Payments\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Log;

/** Bridge to the Messaging automation engine (Phase 4) without a hard dependency on its signature. */
final class Automations
{
    private const ENGINE = 'App\\Domain\\Messaging\\Services\\AutomationEngine';

    /** @param array<string, mixed> $context */
    public static function fire(string $trigger, Tenant $tenant, ?User $client, array $context = []): void
    {
        if (! class_exists(self::ENGINE) || ! method_exists(self::ENGINE, 'fire')) {
            return;
        }
        try {
            $engine = app(self::ENGINE);
            $args = [];
            foreach ((new \ReflectionMethod($engine, 'fire'))->getParameters() as $i => $param) {
                $type = $param->getType() instanceof \ReflectionNamedType ? $param->getType()->getName() : null;
                $args[] = match (true) {
                    $i === 0 => $trigger,
                    $type === User::class => $client,
                    $type === Tenant::class => $tenant,
                    $type === 'array' => $context,
                    $type === 'string' && $param->getName() !== 'trigger' => $client?->id,
                    $param->isDefaultValueAvailable() => $param->getDefaultValue(),
                    default => null,
                };
            }
            $engine->fire(...$args);
        } catch (\Throwable $e) {
            Log::warning('payments.automation_failed', ['trigger' => $trigger, 'error' => $e->getMessage()]);
        }
    }
}
