<?php

namespace App\Notifications\Billing;

use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\AppNotification;

class SubscriptionCancelled extends AppNotification
{
    public function __construct(public readonly Tenant $tenant, public readonly SaasSubscription $subscription, public readonly int $refundMinor) {}

    public function category(): string
    {
        return 'billing';
    }

    public function title(object $notifiable): string
    {
        return __('billing.cancelled.title');
    }

    public function body(object $notifiable): string
    {
        $text = __('billing.cancelled.body', ['date' => $this->subscription->current_period_end?->translatedFormat('j F Y') ?? now()->translatedFormat('j F Y')]);
        if ($this->refundMinor > 0) {
            $text .= ' '.__('billing.cancelled.refund', ['amount' => Money::format($this->refundMinor, $this->subscription->currency)]);
        }

        return $text.' '.__('billing.cancelled.data_kept');
    }

    public function deepLink(): ?string
    {
        return '/app/billing';
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('billing.cancelled.action');
    }
}
