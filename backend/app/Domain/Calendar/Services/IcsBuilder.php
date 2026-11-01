<?php

namespace App\Domain\Calendar\Services;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use Carbon\CarbonImmutable;

/** RFC 5545 VCALENDAR for a booking (download and email attachment). */
final class IcsBuilder
{
    public function forBooking(Booking $booking, Event $event, ?string $locationName = null, ?string $coachName = null): string
    {
        $stamp = fn ($dt) => CarbonImmutable::instance($dt)->utc()->format('Ymd\THis\Z');
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.$this->escape((string) config('brand.name', config('app.name'))).'//Bookings//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:booking-'.$booking->id.'@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'aurum'),
            'DTSTAMP:'.$stamp(now()),
            'DTSTART:'.$stamp($event->starts_at),
            'DTEND:'.$stamp($event->ends_at),
            'SUMMARY:'.$this->escape($event->title),
        ];
        $description = trim(implode("\n", array_filter([$coachName ? __('calendar.ics.with', ['coach' => $coachName]) : null, $event->description, $event->meeting_url, __('calendar.ics.checkin_code', ['code' => $booking->checkin_code])])));
        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$this->escape($description);
        }
        if ($locationName || $event->meeting_url) {
            $lines[] = 'LOCATION:'.$this->escape((string) ($locationName ?? $event->meeting_url));
        }
        if ($event->meeting_url) {
            $lines[] = 'URL:'.$this->escape($event->meeting_url);
        }
        $lines[] = 'STATUS:'.(in_array($booking->status, ['cancelled', 'late_cancel'], true) || $event->status === 'cancelled' ? 'CANCELLED' : 'CONFIRMED');
        array_push($lines, 'BEGIN:VALARM', 'TRIGGER:-PT2H', 'ACTION:DISPLAY', 'DESCRIPTION:'.$this->escape($event->title), 'END:VALARM', 'END:VEVENT', 'END:VCALENDAR');

        return implode("\r\n", array_map(fn (string $l) => $this->fold($l), $lines))."\r\n";
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $value);
    }

    /** Folds lines longer than 75 octets (RFC 5545 §3.1). */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $out = '';
        $current = '';
        foreach (mb_str_split($line) as $char) {
            if (strlen($current.$char) > 74) {
                $out .= ($out === '' ? '' : "\r\n ").$current;
                $current = '';
            }
            $current .= $char;
        }

        return $out.("\r\n ").$current;
    }
}
