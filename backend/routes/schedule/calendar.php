<?php

use App\Domain\Calendar\Services\BookingService;
use App\Domain\Calendar\Services\ReminderService;
use Illuminate\Support\Facades\Schedule;

// 24h and 2h booking reminders (push + email with .ics; WhatsApp is handled by the integrations module).
Schedule::call(fn () => app(ReminderService::class)->sendDue())->name('calendar:reminders')->everyFiveMinutes()->withoutOverlapping()->onOneServer();

// Expire unclaimed waitlist offers and offer the seat to the next person in line.
Schedule::call(fn () => app(BookingService::class)->expireOffers())->name('calendar:waitlist-offers')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
