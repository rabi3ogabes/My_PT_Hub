<?php

namespace App\Filament\Resources\SaasSubscriptions\Pages;

use App\Filament\Resources\SaasSubscriptions\Actions\SubscriptionActions;
use App\Filament\Resources\SaasSubscriptions\SaasSubscriptionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSaasSubscription extends ViewRecord
{
    protected static string $resource = SaasSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SubscriptionActions::cancel(),
            SubscriptionActions::resume(),
            SubscriptionActions::refund(),
        ];
    }
}
