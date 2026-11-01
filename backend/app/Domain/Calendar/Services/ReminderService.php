<?php

namespace App\Domain\Calendar\Services;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Notifications\BookingReminder;
use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;

/** Sends 24h and 2h reminders for booked sessions, each exactly once. */
final class ReminderService
{
    public function sendDue(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $sent = 0;

        $candidates = Booking::withoutGlobalScopes()
            ->with(['event' => fn ($q) => $q->withoutGlobalScopes()->with(['location' => fn ($l) => $l->withoutGlobalScopes(), 'coach'])])
            ->where('status', 'booked')
            ->where(fn ($q) => $q->whereNull('reminded_24h_at')->orWhereNull('reminded_2h_at'))
            ->whereHas('event', fn ($q) => $q->withoutGlobalScopes()->where('status', '!=', 'cancelled')->whereNull('deleted_at')
                ->where('starts_at', '>', $now)->where('starts_at', '<=', $now->addHours(24)))
            ->get();

        foreach ($candidates as $booking) {
            $event = $booking->event;
            if (! $event) {
                continue;
            }
            $within2h = $event->starts_at->lessThanOrEqualTo($now->addHours(2));
            $column = $within2h ? 'reminded_2h_at' : 'reminded_24h_at';
            if ($booking->{$column} !== null) {
                continue;
            }
            $updates = [$column => $now];
            if ($within2h && $booking->reminded_24h_at === null) {
                $updates['reminded_24h_at'] = $now; // never send a stale 24h reminder after the 2h one
            }
            $claimed = Booking::withoutGlobalScopes()->whereKey($booking->id)->whereNull($column)->update($updates);
            if ($claimed === 0) {
                continue;
            }
            $client = User::query()->find($booking->client_id);
            if ($client) {
                $client->notify((new BookingReminder($booking, $event, $within2h ? '2h' : '24h', $event->location?->name, $event->coach?->name))->locale($client->preferredLocale()));
                $sent++;
            }
        }

        return $sent;
    }
}
