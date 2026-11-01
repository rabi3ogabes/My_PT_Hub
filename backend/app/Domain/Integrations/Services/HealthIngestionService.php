<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Models\HealthSample;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Writes normalized samples: unit normalization, idempotent upsert on
 * (user, source, external_id), cross-source time-window dedupe, then
 * rebuilds the affected health_daily rows.
 */
final class HealthIngestionService
{
    /** Unit aliases → multiplier into the canonical unit, per type family. */
    private const CONVERSIONS = [
        'm' => ['m' => 1, 'meter' => 1, 'meters' => 1, 'km' => 1000, 'mi' => 1609.344, 'mile' => 1609.344, 'ft' => 0.3048],
        'kg' => ['kg' => 1, 'g' => 0.001, 'lb' => 0.45359237, 'lbs' => 0.45359237, 'st' => 6.35029318],
        'min' => ['min' => 1, 'minute' => 1, 'minutes' => 1, 'h' => 60, 'hr' => 60, 'hour' => 60, 'hours' => 60, 's' => 1 / 60, 'sec' => 1 / 60, 'seconds' => 1 / 60, 'ms' => 1 / 60000],
        'ml' => ['ml' => 1, 'l' => 1000, 'fl_oz' => 29.5735, 'oz' => 29.5735, 'cup' => 240],
        'kcal' => ['kcal' => 1, 'cal' => 1, 'kj' => 1 / 4.184],
        'ms' => ['ms' => 1, 's' => 1000],
        'pct' => ['pct' => 1, '%' => 1, 'percent' => 1, 'fraction' => 100, 'ratio' => 100],
        'count' => ['count' => 1, 'steps' => 1],
        'bpm' => ['bpm' => 1, 'count/min' => 1],
        'score' => ['score' => 1, 'pct' => 1],
        'mmHg' => ['mmhg' => 1],
    ];

    public function __construct(private readonly HealthNormalizer $normalizer) {}

    /**
     * @param  iterable<SampleData>  $samples
     * @return array{created: int, updated: int, duplicates: int, skipped: int}
     */
    public function ingest(User $user, iterable $samples): array
    {
        $result = ['created' => 0, 'updated' => 0, 'duplicates' => 0, 'skipped' => 0];
        $affected = [];

        DB::transaction(function () use ($user, $samples, &$result, &$affected) {
            foreach ($samples as $sample) {
                $normalized = $this->normalize($sample);
                if ($normalized === null) {
                    $result['skipped']++;

                    continue;
                }

                $externalId = $normalized->externalId ?? 'h:'.sha1(implode('|', [$normalized->type, $normalized->startAt->getTimestamp(), $normalized->endAt->getTimestamp(), round($normalized->value, 3)]));
                $existing = HealthSample::query()->where('user_id', $user->id)->where('source', $normalized->source)->where('external_id', $externalId)->first();

                if ($existing) {
                    $changed = abs($existing->value - $normalized->value) > 0.0005
                        || ! $existing->start_at->equalTo($normalized->startAt)
                        || ! $existing->end_at->equalTo($normalized->endAt);
                    if (! $changed) {
                        $result['duplicates']++;

                        continue;
                    }
                    $affected[] = [$existing->type, $existing->start_at, $existing->end_at];
                    $existing->update(['value' => $normalized->value, 'unit' => $normalized->unit, 'start_at' => $normalized->startAt, 'end_at' => $normalized->endAt, 'meta' => $normalized->meta ?: null]);
                    $result['updated']++;
                    $affected[] = [$normalized->type, $normalized->startAt, $normalized->endAt];

                    continue;
                }

                $row = HealthSample::create([
                    'user_id' => $user->id,
                    'type' => $normalized->type,
                    'value' => $normalized->value,
                    'unit' => $normalized->unit,
                    'start_at' => $normalized->startAt,
                    'end_at' => $normalized->endAt,
                    'source' => $normalized->source,
                    'external_id' => $externalId,
                    'meta' => $normalized->meta ?: null,
                ]);

                $this->dedupeAcrossSources($row) ? $result['duplicates']++ : $result['created']++;
                $affected[] = [$normalized->type, $normalized->startAt, $normalized->endAt];
            }
        });

        if ($affected !== []) {
            $this->normalizer->rebuild($user, $this->affectedDays($user, $affected));
        }

        return $result;
    }

    /** Validates the type and converts the value into the canonical unit; null when unusable. */
    public function normalize(SampleData $sample): ?SampleData
    {
        $types = (array) config('integrations.health.types', []);
        $canonical = $types[$sample->type] ?? null;
        if ($canonical === null || ! is_finite($sample->value) || $sample->value < 0) {
            return null;
        }

        $unit = strtolower(trim($sample->unit));
        $factor = self::CONVERSIONS[$canonical][$unit] ?? (strtolower($canonical) === $unit ? 1 : null);
        if ($factor === null) {
            return null;
        }

        $value = round($sample->value * $factor, 3);
        if ($sample->type === 'body_fat_pct' && $value > 100) {
            return null;
        }

        return new SampleData($sample->type, $value, $canonical, $sample->startAt, $sample->endAt, $sample->source, $sample->externalId, $sample->meta);
    }

    /**
     * Marks the lower-priority sample as a duplicate when another source
     * already recorded the same event. Returns true when $sample is the duplicate.
     */
    public function dedupeAcrossSources(HealthSample $sample): bool
    {
        $rule = config('integrations.health.dedupe.'.$sample->type);
        if (! is_array($rule)) {
            return false;
        }

        $tolerance = (int) $rule['start_tolerance_minutes'];
        $candidates = HealthSample::query()->primary()
            ->where('user_id', $sample->user_id)
            ->where('type', $sample->type)
            ->where('source', '!=', $sample->source)
            ->where('id', '!=', $sample->id)
            ->whereBetween('start_at', [$sample->start_at->copy()->subMinutes($tolerance), $sample->start_at->copy()->addMinutes($tolerance)])
            ->get();

        $match = $candidates->first(function (HealthSample $c) use ($sample, $rule) {
            $a = $sample->durationMinutes();
            $b = $c->durationMinutes();
            $longest = max($a, $b);

            return $longest == 0.0 || (abs($a - $b) / $longest * 100) <= (float) $rule['duration_tolerance_pct'];
        });

        if (! $match) {
            return false;
        }

        if ($this->normalizer->priority($sample->type, $sample->source) < $this->normalizer->priority($match->type, $match->source)) {
            HealthSample::query()->where('duplicate_of_id', $match->id)->update(['duplicate_of_id' => $sample->id]);
            $match->update(['duplicate_of_id' => $sample->id]);

            return false;
        }

        $sample->update(['duplicate_of_id' => $match->id]);

        return true;
    }

    /** Deletes every sample from a source (per-source revoke) and rebuilds aggregates. */
    public function deleteSource(User $user, string $source): int
    {
        return $this->deleteWhere($user, fn ($q) => $q->where('source', $source));
    }

    public function deleteSample(User $user, string $source, string $externalId): int
    {
        return $this->deleteWhere($user, fn ($q) => $q->where('source', $source)->where('external_id', $externalId));
    }

    private function deleteWhere(User $user, \Closure $constraint): int
    {
        $rows = $constraint(HealthSample::query()->where('user_id', $user->id))->get();
        if ($rows->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($rows) {
            $ids = $rows->pluck('id')->all();
            $orphans = HealthSample::query()->whereIn('duplicate_of_id', $ids)->whereNotIn('id', $ids)->get();
            HealthSample::query()->whereIn('id', $ids)->delete();
            foreach ($orphans as $orphan) {
                $orphan->update(['duplicate_of_id' => null]);
            }
            foreach ($orphans as $orphan) {
                $orphan->refresh();
                if ($orphan->duplicate_of_id === null) {
                    $this->dedupeAcrossSources($orphan);
                }
            }
        });

        $this->normalizer->rebuild($user, $this->affectedDays($user, $rows->map(fn (HealthSample $s) => [$s->type, $s->start_at, $s->end_at])->all()));

        return $rows->count();
    }

    /**
     * @param  list<array{0: string, 1: \DateTimeInterface, 2: \DateTimeInterface}>  $items
     * @return array<string, list<string>> local date => types
     */
    private function affectedDays(User $user, array $items): array
    {
        $tz = $user->timezone ?: 'UTC';
        $days = [];
        foreach ($items as [$type, $start, $end]) {
            $date = $this->normalizer->localDate($type, CarbonImmutable::instance($start), CarbonImmutable::instance($end), $tz);
            $days[$date][$type] = $type;
        }

        return array_map('array_values', $days);
    }
}
