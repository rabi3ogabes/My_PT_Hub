<?php

namespace App\Notifications\Marketplace;

use App\Notifications\AppNotification;

/** Asks a client (30 days in) to review their coach; carries a signed submission URL. */
class ReviewRequestNotification extends AppNotification
{
    public function __construct(
        public readonly string $coachName,
        public readonly string $tenantId,
        public readonly string $submitUrl,
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
        return __('marketplace.notifications.review_request_title', ['coach' => $this->coachName]);
    }

    public function body(object $notifiable): string
    {
        return __('marketplace.notifications.review_request_body', ['coach' => $this->coachName]);
    }

    public function deepLink(): ?string
    {
        return '/reviews/new?tenant='.$this->tenantId;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('marketplace.notifications.review_request_action');
    }

    public function pushData(): array
    {
        return ['tenant_id' => $this->tenantId, 'submit_url' => $this->submitUrl];
    }
}
