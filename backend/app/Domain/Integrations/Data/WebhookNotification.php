<?php

namespace App\Domain\Integrations\Data;

/**
 * One provider webhook notification for one external user. It either carries
 * the samples inline (push APIs like Garmin) or references data to fetch.
 */
final class WebhookNotification
{
    /**
     * @param  list<SampleData>  $samples
     * @param  array<string, mixed>  $data  provider reference (resource id, date, collection…)
     */
    public function __construct(
        public readonly string $externalUserId,
        public readonly array $samples = [],
        public readonly array $data = [],
        public readonly bool $deauthorized = false,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'external_user_id' => $this->externalUserId,
            'data' => $this->data,
            'deauthorized' => $this->deauthorized,
            'samples' => array_map(fn (SampleData $s) => [
                'type' => $s->type, 'value' => $s->value, 'unit' => $s->unit,
                'start_at' => $s->startAt->toIso8601String(), 'end_at' => $s->endAt->toIso8601String(),
                'source' => $s->source, 'external_id' => $s->externalId, 'meta' => $s->meta,
            ], $this->samples),
        ];
    }

    /** @param array<string, mixed> $array */
    public static function fromArray(array $array): self
    {
        return new self(
            externalUserId: (string) $array['external_user_id'],
            samples: array_map(fn (array $s) => SampleData::make($s['type'], $s['value'], $s['unit'], $s['start_at'], $s['end_at'], $s['source'], $s['external_id'], $s['meta'] ?? []), $array['samples'] ?? []),
            data: $array['data'] ?? [],
            deauthorized: (bool) ($array['deauthorized'] ?? false),
        );
    }
}
