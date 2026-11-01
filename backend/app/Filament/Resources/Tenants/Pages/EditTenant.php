<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\Tenants\TenantResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->after(fn () => ActivityLogger::log('admin.tenant.deleted', $this->record, tenantId: $this->record->getKey())),
        ];
    }

    protected function afterSave(): void
    {
        ActivityLogger::log('admin.tenant.updated', $this->record, ['changes' => array_keys($this->record->getChanges())], tenantId: $this->record->getKey());
    }
}
