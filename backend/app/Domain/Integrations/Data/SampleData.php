<?php

namespace App\Domain\Integrations\Data;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/** A normalized health sample on its way into health_samples. */
final class SampleData
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $type,
        public readonly float $value,
        public readonly string $unit,
        public readonly CarbonImmutable $startAt,
        public readonly CarbonImmutable $endAt,
        public readonly string $source,
        public readonly ?string $externalId = null,
        public readonly array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function make(string $type, float|int|string $value, string $unit, CarbonInterface|string|int $start, CarbonInterface|string|int|null $end, string $source, string|int|null $externalId = null, array $meta = []): self
    {
        $startAt = self::time($start);
        $endAt = $end === null ? $startAt : self::time($end);

        return new self($type, (float) $value, $unit, $startAt, $endAt->lt($startAt) ? $startAt : $endAt, $source, $externalId === null ? null : (string) $externalId, $meta);
    }

    private static function time(CarbonInterface|string|int $value): CarbonImmutable
    {
        if ($value instanceof CarbonInterface) {
            return CarbonImmutable::instance($value)->utc();
        }

        return is_int($value) ? CarbonImmutable::createFromTimestampUTC($value) : CarbonImmutable::parse($value)->utc();
    }
}
