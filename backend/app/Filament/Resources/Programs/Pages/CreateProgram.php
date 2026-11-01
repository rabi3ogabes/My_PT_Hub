<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Filament\Support\TranslatableData;
use Filament\Resources\Pages\CreateRecord;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] ??= null;
        $data['created_by'] = auth()->id();

        return TranslatableData::clean($data, ['name', 'description']);
    }
}
