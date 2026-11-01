<?php

namespace App\Filament\Resources\SaasPlans\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\SaasPlans\SaasPlanResource;
use Filament\Resources\Pages\EditRecord;

class EditSaasPlan extends EditRecord
{
    protected static string $resource = SaasPlanResource::class;

    protected function afterSave(): void
    {
        ActivityLogger::log('admin.plan.updated', null, [
            'plan' => $this->record->getAttribute('code'),
            'changes' => array_values(array_diff(array_keys($this->record->getChanges()), ['updated_at'])),
        ]);
    }
}
