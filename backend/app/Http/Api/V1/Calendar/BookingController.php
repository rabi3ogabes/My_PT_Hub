<?php

namespace App\Http\Api\V1\Calendar;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use App\Domain\Calendar\Services\BookingService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Calendar\Concerns\ResolvesMembers;
use App\Http\Api\V1\Resources\Calendar\BookingResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Staff booking management: book for a client, attendance (incl. retroactive bulk), QR check-in. */
class BookingController extends ApiController
{
    use ResolvesMembers;

    public function __construct(private readonly BookingService $bookings) {}

    public function store(Request $request, Event $event): JsonResponse
    {
        $this->assertCanManage();
        $data = $request->validate([
            'client_id' => ['required', 'string', 'size:26'],
            'starts_at' => ['nullable', 'date'],
            'occurrence_starts_at' => ['nullable', 'date'],
        ]);
        $client = $this->clientMember($data['client_id']);
        $occurrence = $data['occurrence_starts_at'] ?? $data['starts_at'] ?? null;

        $booking = $this->bookings->book($event, $client, $occurrence ? CarbonImmutable::parse($occurrence) : null, byStaff: true);

        return $this->created(new BookingResource($booking->load('client')));
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $this->assertCanManage();
        $data = $request->validate(['status' => ['required', Rule::in(['booked', 'attended', 'no_show', 'late_cancel', 'cancelled'])]]);
        $booking = $this->bookings->setStatus($booking, $data['status'], $this->user());

        return $this->ok(new BookingResource($booking->load('client')));
    }

    /** Retroactive attendance: e.g. mark last week's sessions attended in one call (atomic). */
    public function bulkStatus(Request $request): JsonResponse
    {
        $this->assertCanManage();
        $data = $request->validate([
            'booking_ids' => ['required', 'array', 'min:1', 'max:500'],
            'booking_ids.*' => ['string', 'size:26'],
            'status' => ['required', Rule::in(['booked', 'attended', 'no_show', 'late_cancel', 'cancelled'])],
        ]);
        $updated = $this->bookings->bulkSetStatus(array_values(array_unique($data['booking_ids'])), $data['status'], $this->user());
        foreach ($updated as $b) {
            $b->loadMissing('client');
        }

        return $this->ok(BookingResource::collection($updated)->resolve($request), ['updated' => count($updated)]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $this->assertCanManage();
        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $booking = $this->bookings->checkIn($data['code'], $this->user());

        return $this->ok(new BookingResource($booking->load('client')));
    }

    private function assertCanManage(): void
    {
        $user = $this->user();
        if (! $user->isOwnerIn() && ! $user->can('bookings.manage')) {
            throw ApiException::forbidden(__('calendar.errors.bookings_forbidden'));
        }
    }
}
