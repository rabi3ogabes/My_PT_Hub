<?php

namespace App\Notifications;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Tenancy\Models\Tenant;

class InvitationNotification extends AppNotification
{
    public function __construct(public readonly Invitation $invitation, public readonly Tenant $tenant, public readonly User $inviter) {}

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
        return __('notifications.invitation.title', ['coach' => $this->inviter->name, 'business' => $this->tenant->name]);
    }

    public function body(object $notifiable): string
    {
        return __('notifications.invitation.body', ['coach' => $this->inviter->firstName(), 'code' => $this->invitation->code]);
    }

    public function deepLink(): ?string
    {
        return '/invite/'.$this->invitation->code;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('notifications.invitation.action');
    }
}
