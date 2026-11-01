<?php

namespace App\Filament\Resources\SaasPlans\Pages;

use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\SaasPlans\SaasPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSaasPlan extends CreateRecord
{
    protected static string $resource = SaasPlanResource::class;

    protected function afterCreate(): void
    {
        ActivityLogger::log('admin.plan.created', null, ['plan' => $this->record->getAttribute('code')]);
    }
}
