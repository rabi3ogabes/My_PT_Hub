<?php

namespace App\Filament\Resources\FeatureFlags\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeatureFlag extends EditRecord
{
    protected static string $resource = FeatureFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        ActivityLogger::log('admin.feature_flag.updated', null, [
            'key' => $this->record->getAttribute('key'),
            'enabled' => (bool) $this->record->getAttribute('enabled'),
            'changes' => array_values(array_diff(array_keys($this->record->getChanges()), ['updated_at'])),
        ]);
    }
}
