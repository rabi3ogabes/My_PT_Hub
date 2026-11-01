<?php

namespace App\Http\Api\V1\Resources\Calendar;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Services\BookingService;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Booking */
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = app(BookingService::class);
        $event = $this->event;

        return [
            'id' => $this->id,
            'event' => $event ? (new EventResource($event))->resolve($request) : null,
            'status' => $this->status,
            'waitlist_position' => $this->waitlist_position !== null ? (int) $this->waitlist_position : null,
            'waitlist_offer_expires_at' => $event && $this->status === 'waitlisted' ? $service->offerExpiresAt($this->resource, $event)?->toIso8601String() : null,
            'checkin_code' => $this->checkin_code,
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'can_cancel_free_until' => $event ? $service->canCancelFreeUntil($event)->toIso8601String() : null,
            'client' => $this->relationLoaded('client') ? UserSummary::make($this->client) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
