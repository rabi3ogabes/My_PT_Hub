<?php

namespace App\Domain\Marketing\Services;

use App\Domain\Calendar\Models\AvailabilityRule;
use App\Domain\Calendar\Models\BookingSetting;
use App\Domain\Calendar\Models\Event;
use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Services\ExternalBusyService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Tenant;
use Carbon\CarbonImmutable;

/**
 * Bookable slots for the public profile. Delegates to the Calendar module's
 * AvailabilityService when present, otherwise derives slots from
 * availability_rules and booking_settings; external calendar busy blocks are
 * always subtracted.
 */
final class PublicAvailabilityService
{
    private const AVAILABILITY_SERVICE = 'App\\Domain\\Calendar\\Services\\AvailabilityService';

    public function __construct(
        private readonly TenantContext $context,
        private readonly ExternalBusyService $busy,
    ) {}

    /**
     * @return list<array{starts_at: string, ends_at: string}>
     */
    public function slots(CoachProfile $profile, CarbonImmutable $from, int $days): array
    {
        $tenant = $profile->tenant ?? Tenant::query()->findOrFail($profile->tenant_id);
        $coach = $profile->user ?? User::query()->findOrFail($profile->user_id);

        return $this->context->run($tenant, function () use ($tenant, $coach, $from, $days) {
            $settings = BookingSetting::query()->where('coach_id', $coach->id)->first();
            if ($settings && ! $settings->public_booking) {
                return [];
            }

            $slots = [];
            for ($i = 0; $i < max(1, min($days, 31)); $i++) {
                foreach ($this->slotsForDate($coach, $tenant, $from->addDays($i)->toDateString(), $settings) as $slot) {
                    $slots[] = $slot;
                }
            }
            if ($slots === []) {
                return [];
            }

            $blocks = $this->busy->busyBlocks($coach, $slots[0]['starts_at'], end($slots)['ends_at']);
            $open = array_filter($slots, function (array $slot) use ($blocks) {
                foreach ($blocks as $b) {
                    if ($b['start']->lt($slot['ends_at']) && $b['end']->gt($slot['starts_at'])) {
                        return false;
                    }
                }

                return true;
            });

            return array_values(array_map(fn (array $s) => [
                'starts_at' => $s['starts_at']->utc()->toIso8601String(),
                'ends_at' => $s['ends_at']->utc()->toIso8601String(),
            ], $open));
        });
    }

    /** @return array{booking_enabled: bool, timezone: string, next_slots: list<array{starts_at: string, ends_at: string}>} */
    public function summary(CoachProfile $profile): array
    {
        $coach = $profile->user ?? User::query()->findOrFail($profile->user_id);
        $slots = $this->slots($profile, CarbonImmutable::now(), 14);

        return [
            'booking_enabled' => $slots !== [],
            'timezone' => $this->timezone($coach, $profile->tenant),
            'next_slots' => array_slice($slots, 0, 3),
        ];
    }

    /**
     * @return list<array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    private function slotsForDate(User $coach, Tenant $tenant, string $date, ?BookingSetting $settings): array
    {
        if (class_exists(self::AVAILABILITY_SERVICE) && method_exists(self::AVAILABILITY_SERVICE, 'slots')) {
            return array_map(fn (array $s) => [
                'starts_at' => CarbonImmutable::instance($s['starts_at']),
                'ends_at' => CarbonImmutable::instance($s['ends_at']),
            ], app(self::AVAILABILITY_SERVICE)->slots($coach, $date, null, $tenant));
        }

        return $this->fromRules($coach, $tenant, $date, $settings);
    }

    /**
     * Fallback slot computation: weekly rules (or date overrides) minus holidays and events.
     *
     * @return list<array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    private function fromRules(User $coach, Tenant $tenant, string $date, ?BookingSetting $settings): array
    {
        $tz = $this->timezone($coach, $tenant);
        $day = CarbonImmutable::parse($date, $tz)->startOfDay();
        $rules = AvailabilityRule::query()->where('coach_id', $coach->id)->get();
        $overrides = $rules->filter(fn (AvailabilityRule $r) => $r->date?->toDateString() === $date);
        if ($overrides->contains(fn (AvailabilityRule $r) => $r->is_unavailable && $r->starts_at === null)) {
            return [];
        }
        $working = $overrides->filter(fn (AvailabilityRule $r) => ! $r->is_unavailable && $r->starts_at !== null);
        if ($working->isEmpty()) {
            $working = $rules->filter(fn (AvailabilityRule $r) => $r->date === null && ! $r->is_unavailable && (int) $r->weekday === $day->dayOfWeekIso && $r->starts_at !== null);
        }

        $length = max(5, (int) ($settings->slot_minutes ?? 60));
        $buffer = (int) ($settings->buffer_minutes ?? 0);
        $earliest = CarbonImmutable::now()->addHours((int) ($settings->min_notice_hours ?? 12));
        $latest = CarbonImmutable::now()->addDays((int) ($settings->max_advance_days ?? 60));

        $busy = Event::query()->where('coach_id', $coach->id)->where('status', '!=', 'cancelled')
            ->where('starts_at', '<', $day->addDay()->utc())->where('ends_at', '>', $day->utc())->get()
            ->map(fn (Event $e) => [CarbonImmutable::instance($e->starts_at)->subMinutes($buffer), CarbonImmutable::instance($e->ends_at)->addMinutes($buffer)])->all();
        foreach ($overrides->filter(fn (AvailabilityRule $r) => $r->is_unavailable && $r->starts_at !== null) as $block) {
            $busy[] = [CarbonImmutable::parse($date.' '.$block->starts_at, $tz)->utc(), CarbonImmutable::parse($date.' '.$block->ends_at, $tz)->utc()];
        }

        $slots = [];
        foreach ($working as $rule) {
            $start = CarbonImmutable::parse($date.' '.$rule->starts_at, $tz)->utc();
            $end = CarbonImmutable::parse($date.' '.$rule->ends_at, $tz)->utc();
            for ($t = $start; $t->addMinutes($length)->lte($end); $t = $t->addMinutes($length + $buffer)) {
                $slotEnd = $t->addMinutes($length);
                if ($t->lt($earliest) || $t->gt($latest)) {
                    continue;
                }
                foreach ($busy as [$bs, $be]) {
                    if ($t->lt($be) && $slotEnd->gt($bs)) {
                        continue 2;
                    }
                }
                $slots[$t->getTimestamp()] = ['starts_at' => $t, 'ends_at' => $slotEnd];
            }
        }
        ksort($slots);

        return array_values($slots);
    }

    private function timezone(User $coach, ?Tenant $tenant): string
    {
        $tz = AvailabilityRule::withoutGlobalScopes()->where('coach_id', $coach->id)->value('timezone');

        return $tz ?: ($coach->timezone && $coach->timezone !== 'UTC' ? $coach->timezone : ($tenant->timezone ?? 'UTC'));
    }
}
