<?php

namespace App\Notifications\Billing;

use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Shared\Support\Money;
use App\Notifications\AppNotification;

class RenewalReminder extends AppNotification
{
    public function __construct(public readonly SaasSubscription $subscription, public readonly int $days) {}

    public function category(): string
    {
        return 'billing';
    }

    public function title(object $notifiable): string
    {
        $key = $this->subscription->status === 'trialing' ? 'billing.reminder.trial_title' : 'billing.reminder.renewal_title';

        return trans_choice($key, $this->days, ['days' => $this->days]);
    }

    public function body(object $notifiable): string
    {
        $date = $this->subscription->current_period_end?->translatedFormat('j F Y');
        if ($this->subscription->status === 'trialing') {
            return __('billing.reminder.trial_body', ['date' => $date]);
        }

        return __('billing.reminder.renewal_body', ['date' => $date, 'amount' => Money::format($this->subscription->price_minor, $this->subscription->currency)]);
    }

    public function deepLink(): ?string
    {
        return '/app/billing';
    }

    public function actionLabel(object $notifiable): ?string
    {
        return __('billing.reminder.action');
    }
}
