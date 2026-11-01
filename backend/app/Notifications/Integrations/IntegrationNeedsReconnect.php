<?php

namespace App\Notifications\Integrations;

use App\Notifications\AppNotification;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Channels\WhatsAppMessage;

/** Sent once when a connection breaks (revoked grant, repeated failures). */
class IntegrationNeedsReconnect extends AppNotification
{
    public function __construct(public readonly string $provider) {}

    public function category(): string
    {
        return 'system';
    }

    public function via(object $notifiable): array
    {
        return [...parent::via($notifiable), WhatsAppChannel::class];
    }

    private function providerName(object $notifiable): string
    {
        return __('integrations.providers.'.$this->provider.'.name', [], $this->localeFor($notifiable));
    }

    private function localeFor(object $notifiable): string
    {
        return method_exists($notifiable, 'preferredLocale') ? $notifiable->preferredLocale() : app()->getLocale();
    }

    public function title(object $notifiable): string
    {
        return __('integrations.notifications.reconnect_title', ['provider' => $this->providerName($notifiable)], $this->localeFor($notifiable));
    }

    public function body(object $notifiable): string
    {
        return __('integrations.notifications.reconnect_body', ['provider' => $this->providerName($notifiable)], $this->localeFor($notifiable));
    }

    public function deepLink(): ?string
    {
        return '/integrations?reconnect='.$this->provider;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('integrations.notifications.reconnect_action', [], $this->localeFor($notifiable));
    }

    public function pushData(): array
    {
        return ['provider' => $this->provider, 'action' => 'reconnect'];
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return new WhatsAppMessage('integration_reconnect', [
            'name' => method_exists($notifiable, 'firstName') ? $notifiable->firstName() : '',
            'provider' => $this->providerName($notifiable),
        ], $this->localeFor($notifiable));
    }
}
