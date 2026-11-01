<?php

namespace App\Domain\Calendar\Services;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\BookingSetting;
use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Calendar\Models\Event;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Services\Notifier;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bookings, waitlists and the append-only credit ledger.
 *
 * Concurrency: every capacity decision happens inside a transaction holding a
 * row lock on the event (classes) or on the coach (1:1 slots); the unique
 * (event_id, client_id) and events.slot_key constraints are the last line of
 * defence against double bookings.
 *
 * Credits: the charge of a booking is always derived from its status and the
 * ledger is reconciled with append-only entries (unique booking_id + reason),
 * so the balance always equals the sum of entries.
 */
final class BookingService
{
    public const STATUSES = ['booked', 'waitlisted', 'attended', 'no_show', 'late_cancel', 'cancelled'];

    /** Statuses occupying a seat. */
    private const SEATED = ['booked', 'attended', 'no_show'];

    /** @var array<string, BookingSetting> */
    private array $settings = [];

    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly EventSeriesService $series,
        private readonly Notifier $notifier,
        private readonly TenantContext $context,
    ) {}

    public function settingsFor(string $coachId): BookingSetting
    {
        return $this->settings[$coachId] ??= $this->availability->settingsFor($coachId);
    }

    public function forgetSettings(): void
    {
        $this->settings = [];
    }

    /**
     * Books a client into an event (class or existing session). For a recurring
     * series, $occurrence selects the occurrence, which is materialized first.
     * Full events put the client on the waitlist.
     */
    public function book(Event $event, User $client, ?CarbonInterface $occurrence = null, bool $byStaff = false): Booking
    {
        $booking = DB::transaction(function () use ($event, $client, $occurrence, $byStaff) {
            if ($event->rrule !== null) {
                if ($occurrence === null) {
                    throw ApiException::invalid('validation_failed', __('calendar.errors.occurrence_required'), ['starts_at' => [__('calendar.errors.occurrence_required')]]);
                }
                $event = $this->series->materialize($event, $occurrence);
            }

            /** @var Event $event */
            $event = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($event->status === 'cancelled' || $event->type === 'block') {
                throw ApiException::invalid('event_not_bookable', __('calendar.errors.event_not_bookable'));
            }
            if (! $byStaff && $event->starts_at->lessThanOrEqualTo(now())) {
                throw ApiException::invalid('event_in_past', __('calendar.errors.event_in_past'));
            }

            $existing = Booking::query()->where('event_id', $event->id)->where('client_id', $client->id)->lockForUpdate()->first();
            if ($existing && $existing->status !== 'cancelled') {
                throw ApiException::conflict('already_booked', __('calendar.errors.already_booked'));
            }

            $overlap = Booking::query()
                ->where('client_id', $client->id)
                ->where('status', 'booked')
                ->where('event_id', '!=', $event->id)
                ->whereHas('event', fn ($q) => $q->where('status', '!=', 'cancelled')->where('starts_at', '<', $event->ends_at)->where('ends_at', '>', $event->starts_at))
                ->exists();
            if ($overlap) {
                throw ApiException::conflict('double_booking', __('calendar.errors.double_booking'));
            }

            $this->assertCredits($client, $event);

            $hasSeat = $this->seatsTaken($event) < (int) $event->capacity;
            $attributes = $hasSeat
                ? ['status' => 'booked', 'waitlist_position' => null, 'waitlist_offered_at' => null]
                : ['status' => 'waitlisted', 'waitlist_position' => ((int) Booking::query()->where('event_id', $event->id)->where('status', 'waitlisted')->max('waitlist_position')) + 1, 'waitlist_offered_at' => null];
            $attributes += ['cancelled_at' => null, 'checked_in_at' => null, 'reminded_24h_at' => null, 'reminded_2h_at' => null];

            if ($existing) {
                $existing->update($attributes);

                return $existing;
            }

            try {
                return DB::transaction(fn () => Booking::create($attributes + [
                    'tenant_id' => $event->tenant_id,
                    'event_id' => $event->id,
                    'client_id' => $client->id,
                    'checkin_code' => $this->newCheckinCode(),
                ]));
            } catch (UniqueConstraintViolationException) {
                throw ApiException::conflict('already_booked', __('calendar.errors.already_booked'));
            }
        });

        $booking->load('event.coach', 'event.location');
        if ($byStaff) {
            $this->notifyClient($booking, 'booked_by_coach');
        }

        return $booking;
    }

    /**
     * Books a 1:1 session in an open availability slot (creates the session event).
     */
    public function bookSlot(User $coach, User $client, CarbonInterface $startsAt, ?Tenant $tenant = null, bool $byStaff = false): Booking
    {
        $startsAt = CarbonImmutable::instance($startsAt)->utc();
        $tenant ??= $this->context->require();

        return DB::transaction(function () use ($coach, $client, $startsAt, $tenant, $byStaff) {
            // Serialize slot bookings per coach.
            User::query()->whereKey($coach->id)->lockForUpdate()->first();

            if (! $this->availability->isOpen($coach, $startsAt, now(), $tenant)) {
                throw ApiException::conflict('slot_unavailable', __('calendar.errors.slot_unavailable'));
            }

            $settings = $this->settingsFor($coach->id);
            try {
                $event = DB::transaction(fn () => Event::create([
                    'tenant_id' => $tenant->id,
                    'coach_id' => $coach->id,
                    'type' => 'session',
                    'title' => __('calendar.session_title', ['client' => $client->name]),
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addMinutes((int) $settings->slot_minutes),
                    'timezone' => $this->availability->timezoneFor($coach, $tenant),
                    'capacity' => 1,
                    'credits_cost' => 1,
                    'status' => 'scheduled',
                    'slot_key' => $coach->id.'|'.$startsAt->format('Y-m-d\TH:i:s\Z'),
                ]));
            } catch (UniqueConstraintViolationException) {
                throw ApiException::conflict('slot_unavailable', __('calendar.errors.slot_unavailable'));
            }

            return $this->book($event, $client, null, $byStaff);
        });
    }

    /** Client or staff cancellation. Late client cancellations forfeit the credit per policy. */
    public function cancel(Booking $booking, bool $byStaff = false): Booking
    {
        $booking = DB::transaction(function () use ($booking, $byStaff) {
            /** @var Booking $booking */
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $event = Event::query()->whereKey($booking->event_id)->lockForUpdate()->firstOrFail();

            if (! in_array($booking->status, ['booked', 'waitlisted'], true)) {
                throw ApiException::conflict('not_cancellable', __('calendar.errors.not_cancellable'));
            }
            if (! $byStaff && $event->starts_at->lessThanOrEqualTo(now())) {
                throw ApiException::conflict('not_cancellable', __('calendar.errors.not_cancellable'));
            }

            $wasWaitlisted = $booking->status === 'waitlisted';
            $settings = $this->settingsFor($event->coach_id);
            $late = ! $byStaff && ! $wasWaitlisted && now()->greaterThan($event->starts_at->copy()->subHours((int) $settings->cancel_window_hours));

            $booking->update([
                'status' => $late ? 'late_cancel' : 'cancelled',
                'cancelled_at' => now(),
                'waitlist_position' => null,
                'waitlist_offered_at' => null,
            ]);
            $booking->setRelation('event', $event);
            $this->reconcileCredits($booking, null);

            if ($wasWaitlisted) {
                $this->renumberWaitlist($event);
            } elseif ($event->slot_key !== null) {
                // A 1:1 slot session: free the slot.
                $event->forceFill(['status' => 'cancelled', 'slot_key' => null])->save();
            } else {
                $this->promote($event);
            }

            return $booking;
        });

        return $booking->load('event.coach', 'event.location');
    }

    /** Claims a waitlist offer within the claim window. */
    public function claim(Booking $booking): Booking
    {
        $booking = DB::transaction(function () use ($booking) {
            /** @var Booking $booking */
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $event = Event::query()->whereKey($booking->event_id)->lockForUpdate()->firstOrFail();

            if ($booking->status !== 'waitlisted' || $booking->waitlist_offered_at === null) {
                throw ApiException::conflict('no_offer', __('calendar.errors.no_offer'));
            }
            if ($this->offerExpired($booking, $event)) {
                throw ApiException::conflict('offer_expired', __('calendar.errors.offer_expired'));
            }

            $booking->update(['status' => 'booked', 'waitlist_position' => null, 'waitlist_offered_at' => null]);
            $this->renumberWaitlist($event);

            return $booking;
        });

        return $booking->load('event.coach', 'event.location');
    }

    /**
     * Staff status change (attendance register, retroactive edits). Credits are
     * reconciled so the ledger reflects the final status.
     */
    public function setStatus(Booking $booking, string $status, ?User $actor = null): Booking
    {
        if (! in_array($status, ['booked', 'attended', 'no_show', 'late_cancel', 'cancelled'], true)) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.invalid_status'), ['status' => [__('calendar.errors.invalid_status')]]);
        }

        $booking = DB::transaction(function () use ($booking, $status, $actor) {
            /** @var Booking $booking */
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $event = Event::query()->withTrashed()->whereKey($booking->event_id)->lockForUpdate()->firstOrFail();
            $previous = $booking->status;

            if ($previous === $status) {
                return $booking->setRelation('event', $event);
            }

            if ($status === 'booked' && $previous !== 'attended' && $previous !== 'no_show' && $this->seatsTaken($event, $booking->id) >= (int) $event->capacity) {
                throw ApiException::conflict('event_full', __('calendar.errors.event_full'));
            }

            $booking->update([
                'status' => $status,
                'waitlist_position' => null,
                'waitlist_offered_at' => null,
                'cancelled_at' => in_array($status, ['cancelled', 'late_cancel'], true) ? ($booking->cancelled_at ?? now()) : null,
                'checked_in_at' => $status === 'attended' ? $booking->checked_in_at : null,
            ]);
            $booking->setRelation('event', $event);
            $this->reconcileCredits($booking, $actor);

            if ($previous === 'waitlisted') {
                $this->renumberWaitlist($event);
            }
            if (in_array($previous, self::SEATED, true) && in_array($status, ['cancelled', 'late_cancel'], true) && $event->starts_at->isFuture()) {
                $this->promote($event);
            }

            return $booking;
        });

        return $booking->load('event.coach', 'event.location');
    }

    /**
     * Applies one status to many bookings atomically (e.g. mark last week's
     * sessions attended in one action).
     *
     * @param  list<string>  $bookingIds
     * @return list<Booking>
     */
    public function bulkSetStatus(array $bookingIds, string $status, ?User $actor = null): array
    {
        return DB::transaction(function () use ($bookingIds, $status, $actor) {
            $bookings = Booking::query()->whereIn('id', $bookingIds)->get();
            if ($bookings->count() !== count(array_unique($bookingIds))) {
                throw new ApiException('not_found', __('calendar.errors.booking_not_found'), 404);
            }

            return $bookings->map(fn (Booking $b) => $this->setStatus($b, $status, $actor))->values()->all();
        });
    }

    /** QR check-in by code: marks the booking attended (deducting credits). */
    public function checkIn(string $code, ?User $actor = null): Booking
    {
        /** @var Booking|null $booking */
        $booking = Booking::query()->where('checkin_code', strtoupper(trim($code)))->first();
        if (! $booking) {
            throw new ApiException('not_found', __('calendar.errors.booking_not_found'), 404);
        }
        $event = Event::query()->withTrashed()->findOrFail($booking->event_id);
        if (! in_array($booking->status, ['booked', 'attended'], true)) {
            throw ApiException::conflict('not_checkinable', __('calendar.errors.not_checkinable'));
        }
        $opens = $event->starts_at->copy()->subHour();
        $closes = $event->ends_at->copy()->addHours(2);
        if (now()->lessThan($opens) || now()->greaterThan($closes)) {
            throw ApiException::conflict('checkin_window', __('calendar.errors.checkin_window'));
        }

        $booking = $this->setStatus($booking, 'attended', $actor);
        if ($booking->checked_in_at === null) {
            $booking->update(['checked_in_at' => now()]);
        }

        return $booking;
    }

    /** Expires waitlist offers past their claim window and offers the seat to the next person. */
    public function expireOffers(): int
    {
        $expired = 0;
        $offers = Booking::withoutGlobalScopes()->with(['event' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('status', 'waitlisted')->whereNotNull('waitlist_offered_at')->get();

        foreach ($offers as $offer) {
            $tenant = Tenant::query()->find($offer->tenant_id);
            if (! $tenant || ! $offer->event) {
                continue;
            }
            $this->context->run($tenant, function () use ($offer, &$expired) {
                DB::transaction(function () use ($offer, &$expired) {
                    $event = Event::query()->whereKey($offer->event_id)->lockForUpdate()->first();
                    $booking = Booking::query()->whereKey($offer->id)->lockForUpdate()->first();
                    if (! $event || ! $booking || $booking->status !== 'waitlisted' || $booking->waitlist_offered_at === null || ! $this->offerExpired($booking, $event)) {
                        return;
                    }
                    $booking->update(['status' => 'cancelled', 'cancelled_at' => now(), 'waitlist_position' => null, 'waitlist_offered_at' => null]);
                    $expired++;
                    $this->renumberWaitlist($event);
                    $this->promote($event);
                });
            });
        }

        return $expired;
    }

    public function balance(string $clientId): int
    {
        return CreditLedgerEntry::balanceFor($clientId);
    }

    /** Manual credit adjustment (purchase corrections, goodwill, expiry) — always with a reason. */
    public function adjustCredits(User $client, int $amount, string $note, ?User $actor = null, ?string $expiresOn = null, string $reason = 'adjustment'): CreditLedgerEntry
    {
        if ($amount === 0) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.zero_amount'), ['amount' => [__('calendar.errors.zero_amount')]]);
        }

        return CreditLedgerEntry::create([
            'client_id' => $client->id,
            'amount' => $amount,
            'reason' => $reason,
            'note' => $note,
            'created_by' => $actor?->id,
            'expires_on' => $expiresOn,
        ]);
    }

    /** Fresh capacity view: seats taken by booked/attended/no-show plus open waitlist offers. */
    public function seatsTaken(Event $event, ?string $exceptBookingId = null): int
    {
        $claimMinutes = (int) $this->settingsFor($event->coach_id)->waitlist_claim_minutes;

        return Booking::query()
            ->where('event_id', $event->id)
            ->when($exceptBookingId, fn ($q) => $q->where('id', '!=', $exceptBookingId))
            ->where(function ($q) use ($claimMinutes) {
                $q->whereIn('status', self::SEATED)
                    ->orWhere(fn ($w) => $w->where('status', 'waitlisted')->whereNotNull('waitlist_offered_at')->where('waitlist_offered_at', '>', now()->subMinutes($claimMinutes)));
            })
            ->count();
    }

    public function canCancelFreeUntil(Event $event): CarbonImmutable
    {
        return CarbonImmutable::instance($event->starts_at)->subHours((int) $this->settingsFor($event->coach_id)->cancel_window_hours);
    }

    public function offerExpiresAt(Booking $booking, Event $event): ?CarbonImmutable
    {
        return $booking->waitlist_offered_at
            ? CarbonImmutable::instance($booking->waitlist_offered_at)->addMinutes((int) $this->settingsFor($event->coach_id)->waitlist_claim_minutes)
            : null;
    }

    /** Offers / gives freed seats to the waitlist in position order. Called inside a transaction holding the event lock. */
    private function promote(Event $event): void
    {
        if ($event->starts_at->isPast() || $event->status === 'cancelled') {
            return;
        }
        $claimMinutes = (int) $this->settingsFor($event->coach_id)->waitlist_claim_minutes;
        $free = (int) $event->capacity - $this->seatsTaken($event);

        $queue = Booking::query()->where('event_id', $event->id)->where('status', 'waitlisted')->whereNull('waitlist_offered_at')
            ->orderBy('waitlist_position')->limit(max(0, $free))->get();

        foreach ($queue as $next) {
            if ($claimMinutes === 0) {
                $next->update(['status' => 'booked', 'waitlist_position' => null]);
                $next->setRelation('event', $event);
                $this->notifyClient($next, 'promoted');
            } else {
                $next->update(['waitlist_offered_at' => now()]);
                $next->setRelation('event', $event);
                $this->notifyClient($next, 'offer', ['minutes' => $claimMinutes]);
            }
        }
        if ($claimMinutes === 0 && $queue->isNotEmpty()) {
            $this->renumberWaitlist($event);
        }
    }

    private function renumberWaitlist(Event $event): void
    {
        $position = 1;
        foreach (Booking::query()->where('event_id', $event->id)->where('status', 'waitlisted')->orderBy('waitlist_position')->orderBy('created_at')->get() as $b) {
            if ((int) $b->waitlist_position !== $position) {
                $b->update(['waitlist_position' => $position]);
            }
            $position++;
        }
    }

    private function offerExpired(Booking $booking, Event $event): bool
    {
        $expires = $this->offerExpiresAt($booking, $event);

        return $expires !== null && now()->greaterThan($expires);
    }

    /** The credit charge a booking should carry for its current status (negative = deduction). */
    public function chargeFor(Booking $booking, Event $event): int
    {
        $cost = (int) $event->credits_cost;
        if ($cost <= 0) {
            return 0;
        }
        $policy = $this->settingsFor($event->coach_id)->no_show_policy;

        return match ($booking->status) {
            'attended' => -$cost,
            'no_show' => $policy === 'forfeit' ? -$cost : 0,
            'late_cancel' => $policy === 'none' ? 0 : -$cost,
            default => 0,
        };
    }

    /** Appends ledger entries so that sum(entries for booking) == chargeFor(booking). */
    private function reconcileCredits(Booking $booking, ?User $actor): void
    {
        $event = $booking->relationLoaded('event') ? $booking->event : Event::query()->withTrashed()->findOrFail($booking->event_id);
        $desired = $this->chargeFor($booking, $event);
        $entries = CreditLedgerEntry::query()->where('booking_id', $booking->id)->get();
        $diff = $desired - (int) $entries->sum('amount');
        if ($diff === 0) {
            return;
        }

        $used = $entries->pluck('reason')->all();
        $candidates = $diff < 0 ? [$booking->status, 'adjustment'] : ['refund', 'adjustment'];
        $reason = collect($candidates)->first(fn (string $r) => ! in_array($r, $used, true));
        if ($reason === null) {
            throw ApiException::conflict('credit_history_locked', __('calendar.errors.credit_history_locked'));
        }

        CreditLedgerEntry::create([
            'tenant_id' => $booking->tenant_id,
            'client_id' => $booking->client_id,
            'amount' => $diff,
            'reason' => $reason,
            'booking_id' => $booking->id,
            'created_by' => $actor?->id,
            'note' => $event->title,
        ]);
    }

    private function assertCredits(User $client, Event $event): void
    {
        $tenant = $this->context->get();
        if ((int) $event->credits_cost <= 0 || ! $tenant || ! $tenant->setting('calendar.require_credits', false)) {
            return;
        }
        if ($this->balance($client->id) < (int) $event->credits_cost) {
            throw new ApiException('insufficient_credits', __('calendar.errors.insufficient_credits'), 402);
        }
    }

    private function newCheckinCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
            $code = preg_replace('/[^A-Z0-9]/', 'X', $code) ?? $code;
        } while (Booking::withoutGlobalScopes()->where('checkin_code', $code)->exists());

        return $code;
    }

    /** @param  array<string, mixed>  $extra */
    private function notifyClient(Booking $booking, string $kind, array $extra = []): void
    {
        $client = $booking->relationLoaded('client') ? $booking->client : User::query()->find($booking->client_id);
        $event = $booking->relationLoaded('event') ? $booking->event : Event::query()->withTrashed()->find($booking->event_id);
        if (! $client || ! $event) {
            return;
        }
        $when = CarbonImmutable::instance($event->starts_at)->setTimezone($client->timezone ?: $event->timezone);

        DB::afterCommit(fn () => $this->notifier->send(
            $client,
            'bookings',
            "calendar.notifications.{$kind}.title",
            "calendar.notifications.{$kind}.body",
            ['event' => $event->title, 'time' => $when->format('Y-m-d H:i'), ...$extra],
            '/bookings/'.$booking->id,
            ['booking_id' => $booking->id, 'event_id' => $event->id],
        ));
    }
}
