<?php

namespace App\Notifications\Channels;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

/**
 * Notification channel for WhatsApp Business templates. Notifications opt in
 * by listing WhatsAppChannel::class in via() and implementing
 * toWhatsApp($notifiable): ?WhatsAppMessage. Users without an active
 * WhatsApp opt-in are skipped silently.
 */
class WhatsAppChannel
{
    public function __construct(private readonly WhatsAppService $whatsapp) {}

    public function send(object $notifiable, Notification $notification): ?string
    {
        if (! $notifiable instanceof User || ! method_exists($notification, 'toWhatsApp')) {
            return null;
        }
        $message = $notification->toWhatsApp($notifiable);
        if (! $message instanceof WhatsAppMessage || ! $this->whatsapp->isOptedIn($notifiable)) {
            return null;
        }

        return $this->whatsapp->sendTemplate($notifiable, $message->template, $message->params, $message->locale ?? $notifiable->preferredLocale());
    }
}
