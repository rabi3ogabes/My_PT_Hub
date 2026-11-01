<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        ActivityLogger::log('admin.tenant.created', $this->record, tenantId: $this->record->getKey());
    }
}
