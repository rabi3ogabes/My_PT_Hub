<?php

namespace App\Notifications\Billing;

use App\Domain\Billing\Models\SaasSubscription;
use App\Notifications\AppNotification;

class PaymentFailed extends AppNotification
{
    public function __construct(public readonly SaasSubscription $subscription) {}

    public function category(): string
    {
        return 'billing';
    }

    public function title(object $notifiable): string
    {
        return __('billing.payment_failed.title');
    }

    public function body(object $notifiable): string
    {
        return __('billing.payment_failed.body');
    }

    public function deepLink(): ?string
    {
        return '/app/billing';
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('billing.payment_failed.action');
    }
}
