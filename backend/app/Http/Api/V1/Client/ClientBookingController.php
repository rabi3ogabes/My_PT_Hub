<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use App\Domain\Calendar\Services\AvailabilityService;
use App\Domain\Calendar\Services\BookingService;
use App\Domain\Calendar\Services\EventSeriesService;
use App\Domain\Calendar\Services\IcsBuilder;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Calendar\CreditController;
use App\Http\Api\V1\Resources\Calendar\BookingResource;
use App\Http\Api\V1\Resources\Calendar\EventResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/** Client self-service bookings: slots, classes, book / cancel / claim, .ics, credits. */
class ClientBookingController extends ApiController
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly AvailabilityService $availability,
        private readonly EventSeriesService $series,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['filter.status' => ['nullable', Rule::in(['upcoming', 'past', 'all'])]]);
        $filter = $request->input('filter.status', 'upcoming');
        $now = now();

        $bookings = Booking::query()->where('client_id', $this->user()->id)
            ->whereHas('event', fn ($q) => $q->withTrashed()->when($filter === 'upcoming', fn ($e) => $e->where('ends_at', '>=', $now))->when($filter === 'past', fn ($e) => $e->where('ends_at', '<', $now)))
            ->when($filter === 'upcoming', fn ($q) => $q->whereIn('status', ['booked', 'waitlisted']))
            ->with(['event' => fn ($q) => $q->withTrashed()->with(['coach', 'location'])])
            ->get()
            ->sortBy(fn (Booking $b) => $b->event->starts_at->getTimestamp(), SORT_REGULAR, $filter === 'past')
            ->values();

        return $this->ok(BookingResource::collection($bookings)->resolve($request));
    }

    /** Open 1:1 slots for a coach on a coach-local date (defaults to the client's coach). */
    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'coach_id' => ['nullable', 'string', 'size:26'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $coach = $this->coach($data['coach_id'] ?? null);
        $slots = $this->availability->slots($coach, $data['date'], now(), $this->tenant());

        return $this->ok(array_map(fn (array $s) => [
            'starts_at' => $s['starts_at']->toIso8601String(),
            'ends_at' => $s['ends_at']->toIso8601String(),
        ], $slots), ['coach_id' => $coach->id, 'timezone' => $this->availability->timezoneFor($coach, $this->tenant())]);
    }

    public function classes(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'coach_id' => ['nullable', 'string', 'size:26'],
        ]);
        $from = isset($data['from']) ? CarbonImmutable::parse($data['from'])->utc() : CarbonImmutable::now()->utc();
        $to = isset($data['to']) ? CarbonImmutable::parse($data['to'])->utc() : $from->addDays(14);
        if (! $to->greaterThan($from) || $from->diffInDays($to) > 62) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.range_too_long'), ['to' => [__('calendar.errors.range_too_long')]]);
        }

        $query = Event::query()->with(['coach', 'location'])
            ->whereIn('type', ['class', 'event', 'live'])->where('status', 'scheduled')
            ->when($data['coach_id'] ?? null, fn ($q, $id) => $q->where('coach_id', $id))
            ->withCount([
                'bookings as booked_count' => fn ($q) => $q->whereIn('status', ['booked', 'attended', 'no_show']),
                'bookings as waitlist_count' => fn ($q) => $q->where('status', 'waitlisted'),
            ]);

        $events = array_values(array_filter($this->series->expand($query, $from, $to), fn (Event $e) => $e->ends_at->isFuture()));

        return $this->ok(EventResource::collection($events)->resolve($request));
    }

    /** Book a class ({event_id[, starts_at for a recurring occurrence]}) or a 1:1 slot ({coach_id, starts_at}). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required_without:coach_id', 'nullable', 'string', 'size:26'],
            'coach_id' => ['required_without:event_id', 'nullable', 'string', 'size:26'],
            'starts_at' => ['required_with:coach_id', 'nullable', 'date'],
        ]);
        $client = $this->user();
        if (! $client->isClientIn()) {
            throw ApiException::forbidden(__('calendar.errors.clients_only'));
        }

        if (! empty($data['event_id'])) {
            /** @var Event|null $event */
            $event = Event::query()->find($data['event_id']);
            if (! $event || ! in_array($event->type, ['class', 'event', 'live'], true)) {
                throw new ApiException('not_found', __('calendar.errors.event_not_found'), 404);
            }
            $booking = $this->bookings->book($event, $client, isset($data['starts_at']) ? CarbonImmutable::parse($data['starts_at']) : null);
        } else {
            $coach = $this->coach($data['coach_id']);
            $booking = $this->bookings->bookSlot($coach, $client, CarbonImmutable::parse($data['starts_at']), $this->tenant());
        }

        return $this->created(new BookingResource($booking));
    }

    public function cancel(Booking $booking): JsonResponse
    {
        return $this->ok(new BookingResource($this->bookings->cancel($this->own($booking))));
    }

    public function claim(Booking $booking): JsonResponse
    {
        return $this->ok(new BookingResource($this->bookings->claim($this->own($booking))));
    }

    public function ics(Booking $booking, IcsBuilder $ics): Response
    {
        $booking = $this->own($booking);
        $event = Event::query()->withTrashed()->with(['coach', 'location'])->findOrFail($booking->event_id);

        return response($ics->forBooking($booking, $event, $event->location?->name, $event->coach?->name), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="booking-'.$booking->id.'.ics"',
        ]);
    }

    public function credits(): JsonResponse
    {
        return $this->ok(CreditController::summary($this->user()->id, $this->bookings));
    }

    private function own(Booking $booking): Booking
    {
        if ($booking->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('calendar.errors.booking_not_found'), 404);
        }

        return $booking;
    }

    private function coach(?string $coachId): User
    {
        if ($coachId === null) {
            $coachId = ClientProfile::query()->where('user_id', $this->user()->id)->value('coach_id')
                ?? Membership::query()->where('role', 'owner')->where('status', 'active')->value('user_id');
        }
        $isStaff = $coachId && Membership::query()->where('user_id', $coachId)->where('status', 'active')->whereIn('role', ['owner', 'coach', 'assistant'])->exists();
        $coach = $isStaff ? User::query()->find($coachId) : null;
        if (! $coach) {
            throw new ApiException('not_found', __('calendar.errors.coach_not_found'), 404);
        }

        return $coach;
    }
}
