<?php

namespace App\Notifications\Marketplace;

use App\Notifications\AppNotification;

/** Tells the coach a prospect contacted them from the public profile or the match quiz. */
class NewMarketplaceLead extends AppNotification
{
    public function __construct(
        public readonly string $leadId,
        public readonly string $tenantId,
        public readonly string $leadName,
        public readonly string $origin,
    ) {}

    public function category(): string
    {
        return 'marketing';
    }

    protected function defaultEmail(): bool
    {
        return true;
    }

    public function title(object $notifiable): string
    {
        return __('marketplace.notifications.new_lead_title', ['name' => $this->leadName]);
    }

    public function body(object $notifiable): string
    {
        return __('marketplace.notifications.new_lead_body_'.($this->origin === 'match' ? 'match' : 'profile'), ['name' => $this->leadName]);
    }

    public function deepLink(): ?string
    {
        return '/crm/leads/'.$this->leadId;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('marketplace.notifications.new_lead_action');
    }

    public function pushData(): array
    {
        return ['lead_id' => $this->leadId, 'tenant_id' => $this->tenantId];
    }
}
