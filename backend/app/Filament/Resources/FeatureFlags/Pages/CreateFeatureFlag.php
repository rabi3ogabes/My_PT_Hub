<?php

namespace App\Filament\Resources\FeatureFlags\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeatureFlag extends CreateRecord
{
    protected static string $resource = FeatureFlagResource::class;

    protected function afterCreate(): void
    {
        ActivityLogger::log('admin.feature_flag.created', null, ['key' => $this->record->getAttribute('key')]);
    }
}
