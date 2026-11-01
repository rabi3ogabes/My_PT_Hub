<?php

namespace App\Filament\Resources\CoachProfiles\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\CoachProfiles\CoachProfileResource;
use App\Filament\Support\TranslatableData;
use Filament\Resources\Pages\EditRecord;

class EditCoachProfile extends EditRecord
{
    protected static string $resource = CoachProfileResource::class;

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TranslatableData::clean($data, ['bio']);
    }

    protected function afterSave(): void
    {
        ActivityLogger::log('admin.coach_profile.updated', null, [
            'coach_profile_id' => $this->record->getKey(),
            'changes' => array_values(array_diff(array_keys($this->record->getChanges()), ['updated_at'])),
        ], tenantId: $this->record->getAttribute('tenant_id'));
    }
}
