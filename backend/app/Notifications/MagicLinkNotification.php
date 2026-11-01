<?php

namespace App\Notifications;

class MagicLinkNotification extends AppNotification
{
    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function category(): string
    {
        return 'system';
    }

    public function title(object $notifiable): string
    {
        return __('auth.magic_link.title', ['brand' => config('brand.name')]);
    }

    public function body(object $notifiable): string
    {
        return __('auth.magic_link.body');
    }

    public function deepLink(): ?string
    {
        return '/auth/magic/'.$this->token;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('auth.magic_link.action');
    }
}
