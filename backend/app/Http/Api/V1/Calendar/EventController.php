<?php

namespace App\Http\Api\V1\Calendar;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use App\Domain\Calendar\Models\Location;
use App\Domain\Calendar\Services\EventSeriesService;
use App\Domain\Calendar\Services\RecurrenceService;
use App\Domain\Messaging\Services\Notifier;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Calendar\Concerns\ResolvesMembers;
use App\Http\Api\V1\Resources\Calendar\BookingResource;
use App\Http\Api\V1\Resources\Calendar\EventResource;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends ApiController
{
    use ResolvesMembers;

    public const TYPES = ['session', 'class', 'event', 'live', 'block'];

    public function __construct(
        private readonly EventSeriesService $series,
        private readonly RecurrenceService $rrule,
        private readonly Notifier $notifier,
    ) {}

    /** Calendar range (recurrences expanded). The client keeps its selected week; this endpoint is stateless. */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
            'coach_id' => ['nullable', 'string', 'size:26'],
            'location_id' => ['nullable', 'string', 'size:26'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'include_cancelled' => ['nullable', 'boolean'],
        ]);
        $from = CarbonImmutable::parse($data['from'])->utc();
        $to = CarbonImmutable::parse($data['to'])->utc();
        if ($from->diffInDays($to) > 93) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.range_too_long'), ['to' => [__('calendar.errors.range_too_long')]]);
        }

        $events = $this->series->expand($this->baseQuery($data), $from, $to);

        return $this->ok(EventResource::collection($events)->resolve($request), ['from' => $from->toIso8601String(), 'to' => $to->toIso8601String()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, true);
        $data['coach_id'] = $this->staffMember($data['coach_id'] ?? $this->user()->id)->id;
        $data['timezone'] ??= $this->tenant()->timezone ?: 'UTC';
        $data['status'] = 'scheduled';
        if ($data['type'] === 'session' && ! isset($data['capacity'])) {
            $data['capacity'] = 1;
        }
        $event = Event::create($data);

        return $this->created(new EventResource($this->withCounts($event)));
    }

    /** Event detail with its attendance register. For a series, ?occurrence_starts_at= selects an occurrence. */
    public function show(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate(['occurrence_starts_at' => ['nullable', 'date']]);
        if ($event->rrule !== null && ! empty($data['occurrence_starts_at'])) {
            $start = CarbonImmutable::parse($data['occurrence_starts_at'])->utc();
            $child = Event::query()->where('series_id', $event->id)->where('recurrence_id', $start)->first();
            if ($child) {
                $event = $child;
            } else {
                $this->series->assertOccurrence($event, $start);
                $event = $this->series->occurrence($event->load(['coach', 'location']), $start);
            }
        }

        $bookings = $event->isOccurrence ? collect() : Booking::query()->where('event_id', $event->id)->with('client')
            ->orderByRaw("case status when 'waitlisted' then 1 else 0 end")->orderBy('waitlist_position')->orderBy('created_at')->get();

        $payload = (new EventResource($event->isOccurrence ? $event : $this->withCounts($event)))->resolve($request);
        $payload['exdates'] = $event->exdates ?? [];
        $payload['bookings'] = $bookings->map(fn (Booking $b) => [
            'id' => $b->id,
            'client' => UserSummary::make($b->client),
            'status' => $b->status,
            'waitlist_position' => $b->waitlist_position,
            'checked_in_at' => $b->checked_in_at?->toIso8601String(),
        ])->values()->all();

        return $this->ok($payload);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $data = $this->validated($request, false);
        $scope = $request->validate(['scope' => ['nullable', Rule::in(['this', 'future', 'all'])], 'occurrence_starts_at' => ['nullable', 'date']]);
        if (isset($data['coach_id'])) {
            $data['coach_id'] = $this->staffMember($data['coach_id'])->id;
        }
        $updated = $this->series->update(
            $event,
            $data,
            $scope['scope'] ?? 'all',
            isset($scope['occurrence_starts_at']) ? CarbonImmutable::parse($scope['occurrence_starts_at']) : null,
        );

        return $this->ok(new EventResource($this->withCounts($updated)));
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $scope = $request->validate(['scope' => ['nullable', Rule::in(['this', 'future', 'all'])], 'occurrence_starts_at' => ['nullable', 'date']]);
        $cancelled = $this->series->delete(
            $event,
            $scope['scope'] ?? 'all',
            isset($scope['occurrence_starts_at']) ? CarbonImmutable::parse($scope['occurrence_starts_at']) : null,
        );

        foreach ($cancelled as $booking) {
            $booking->loadMissing(['client', 'event' => fn ($q) => $q->withTrashed()]);
            if ($booking->client && $booking->event) {
                $this->notifier->send($booking->client, 'bookings', 'calendar.notifications.event_cancelled.title', 'calendar.notifications.event_cancelled.body', [
                    'event' => $booking->event->title,
                    'time' => CarbonImmutable::instance($booking->event->starts_at)->setTimezone($booking->client->timezone ?: $booking->event->timezone)->format('Y-m-d H:i'),
                ], '/bookings/'.$booking->id, ['booking_id' => $booking->id]);
            }
        }

        return $this->ok(['cancelled_bookings' => BookingResource::collection(collect($cancelled)->each->load(['event' => fn ($q) => $q->withTrashed()]))->resolve($request)]);
    }

    /** @param  array<string, mixed>  $filters */
    private function baseQuery(array $filters): Builder
    {
        return Event::query()->with(['coach', 'location'])
            ->withCount([
                'bookings as booked_count' => fn ($q) => $q->whereIn('status', ['booked', 'attended', 'no_show']),
                'bookings as waitlist_count' => fn ($q) => $q->where('status', 'waitlisted'),
            ])
            ->when($filters['coach_id'] ?? null, fn ($q, $id) => $q->where('coach_id', $id))
            ->when($filters['location_id'] ?? null, fn ($q, $id) => $q->where('location_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when(empty($filters['include_cancelled']), fn ($q) => $q->where('status', '!=', 'cancelled'));
    }

    private function withCounts(Event $event): Event
    {
        return $event->load(['coach', 'location'])->loadCount([
            'bookings as booked_count' => fn ($q) => $q->whereIn('status', ['booked', 'attended', 'no_show']),
            'bookings as waitlist_count' => fn ($q) => $q->where('status', 'waitlisted'),
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';
        $data = $request->validate([
            'type' => [$req, Rule::in(self::TYPES)],
            'title' => [$req, 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => [$req, 'date'],
            'ends_at' => [$req, 'date'],
            'timezone' => ['nullable', 'timezone:all'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'credits_cost' => ['nullable', 'integer', 'min:0', 'max:100'],
            'coach_id' => ['nullable', 'string', 'size:26'],
            'location_id' => ['nullable', 'string', 'size:26'],
            'meeting_url' => ['nullable', 'url', 'max:500'],
            'rrule' => ['nullable', 'string', 'max:255'],
        ]);
        foreach (['starts_at', 'ends_at'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = CarbonImmutable::parse($data[$field])->utc();
            }
        }
        if (isset($data['starts_at'], $data['ends_at']) && ! $data['ends_at']->greaterThan($data['starts_at'])) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.invalid_times'), ['ends_at' => [__('calendar.errors.invalid_times')]]);
        }
        if (! empty($data['rrule'])) {
            try {
                $this->rrule->parse($data['rrule']);
            } catch (\InvalidArgumentException) {
                throw ApiException::invalid('validation_failed', __('calendar.errors.invalid_rrule'), ['rrule' => [__('calendar.errors.invalid_rrule')]]);
            }
            $data['rrule'] = strtoupper(preg_replace('/^RRULE:/i', '', trim($data['rrule'])) ?? '');
        }
        if (! empty($data['location_id']) && ! Location::query()->whereKey($data['location_id'])->exists()) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.location_not_found'), ['location_id' => [__('calendar.errors.location_not_found')]]);
        }

        return array_filter($data, fn ($v, $k) => $v !== null || in_array($k, ['description', 'location_id', 'meeting_url', 'rrule'], true), ARRAY_FILTER_USE_BOTH);
    }
}
