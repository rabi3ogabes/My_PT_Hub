<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\Actions\TenantActions;
use App\Filament\Resources\Tenants\TenantResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            TenantActions::changePlan(),
            TenantActions::suspend(),
            TenantActions::unsuspend(),
            TenantActions::impersonateOwner(),
        ];
    }
}
