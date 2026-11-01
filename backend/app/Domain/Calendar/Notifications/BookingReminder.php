<?php

namespace App\Domain\Calendar\Notifications;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use App\Domain\Calendar\Services\IcsBuilder;
use App\Domain\Identity\Models\User;
use App\Notifications\AppNotification;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Messages\MailMessage;

/** 24h / 2h booking reminder (push + email with an .ics attachment). */
class BookingReminder extends AppNotification
{
    public function __construct(
        public readonly Booking $booking,
        public readonly Event $event,
        public readonly string $window,
        public readonly ?string $locationName = null,
        public readonly ?string $coachName = null,
    ) {}

    public function category(): string
    {
        return 'bookings';
    }

    public function title(object $notifiable): string
    {
        return (string) __('calendar.notifications.reminder_'.$this->window.'.title', ['event' => $this->event->title]);
    }

    public function body(object $notifiable): string
    {
        $tz = $notifiable instanceof User && $notifiable->timezone ? $notifiable->timezone : $this->event->timezone;

        return (string) __('calendar.notifications.reminder_'.$this->window.'.body', [
            'event' => $this->event->title,
            'time' => CarbonImmutable::instance($this->event->starts_at)->setTimezone($tz)->format('H:i'),
        ]);
    }

    public function deepLink(): ?string
    {
        return '/bookings/'.$this->booking->id;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return (string) __('calendar.notifications.view_booking');
    }

    public function pushData(): array
    {
        return ['booking_id' => $this->booking->id, 'event_id' => $this->event->id];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return parent::toMail($notifiable)->attachData(
            app(IcsBuilder::class)->forBooking($this->booking, $this->event, $this->locationName, $this->coachName),
            'booking.ics',
            ['mime' => 'text/calendar; charset=utf-8; method=PUBLISH'],
        );
    }
}
