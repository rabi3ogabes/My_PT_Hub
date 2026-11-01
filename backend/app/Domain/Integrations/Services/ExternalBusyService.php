<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Data\BusyBlock;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Busy intervals from a coach's connected external calendars (Google,
 * Outlook) used by availability to remove slots. Results are cached briefly
 * and invalidated by calendar push notifications.
 */
final class ExternalBusyService
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly IntegrationManager $manager,
    ) {}

    /**
     * Merged, sorted busy blocks across all connected calendars of the coach.
     *
     * @return list<array{start: CarbonImmutable, end: CarbonImmutable, source: string}>
     */
    public function busyBlocks(User $coach, CarbonInterface $from, CarbonInterface $to): array
    {
        $blocks = [];
        foreach ($this->calendarIntegrations($coach) as $integration) {
            array_push($blocks, ...$this->forIntegration($integration, $from, $to));
        }

        usort($blocks, fn (BusyBlock $a, BusyBlock $b) => $a->start <=> $b->start);

        return array_map(fn (BusyBlock $b) => ['start' => $b->start, 'end' => $b->end, 'source' => $b->source], $blocks);
    }

    /** True when [start, end) overlaps any external busy block. */
    public function isBusy(User $coach, CarbonInterface $start, CarbonInterface $end): bool
    {
        foreach ($this->busyBlocks($coach, $start, $end) as $block) {
            if ($block['start']->lt($end) && $block['end']->gt($start)) {
                return true;
            }
        }

        return false;
    }

    /** @return Collection<int, Integration> */
    public function calendarIntegrations(User $coach): Collection
    {
        return Integration::query()->where('user_id', $coach->id)
            ->whereIn('provider', $this->registry->keysByCategory('calendar'))
            ->where('status', 'connected')->get();
    }

    public function invalidate(Integration $integration): void
    {
        Cache::forever($this->versionKey($integration), (int) Cache::get($this->versionKey($integration), 0) + 1);
    }

    /** @return list<BusyBlock> */
    private function forIntegration(Integration $integration, CarbonInterface $from, CarbonInterface $to): array
    {
        $from = CarbonImmutable::instance($from)->utc();
        $to = CarbonImmutable::instance($to)->utc();
        $key = sprintf('integrations:busy:%s:%d:%d:%d', $integration->id, (int) Cache::get($this->versionKey($integration), 0), $from->getTimestamp(), $to->getTimestamp());

        try {
            $raw = Cache::remember($key, (int) config('integrations.calendar.busy_cache_seconds', 300), function () use ($integration, $from, $to) {
                $fresh = $this->manager->withFreshToken($integration);

                return array_map(fn (BusyBlock $b) => [$b->start->toIso8601String(), $b->end->toIso8601String(), $b->source],
                    $this->registry->calendar($integration->provider)->busyBlocks($fresh, $from, $to));
            });
        } catch (ProviderAuthException $e) {
            $this->manager->markNeedsReconnect($integration, $e->getMessage());

            return [];
        } catch (Throwable $e) {
            Log::warning('integrations.busy_failed', ['integration' => $integration->id, 'error' => $e->getMessage()]);

            return [];
        }

        return array_map(fn (array $r) => new BusyBlock(CarbonImmutable::parse($r[0])->utc(), CarbonImmutable::parse($r[1])->utc(), $r[2]), $raw);
    }

    private function versionKey(Integration $integration): string
    {
        return 'integrations:busy-version:'.$integration->id;
    }
}
