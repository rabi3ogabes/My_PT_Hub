<?php

namespace App\Filament\Resources\Foods\Pages;

use App\Filament\Resources\Foods\FoodResource;
use App\Filament\Support\TranslatableData;
use Filament\Resources\Pages\CreateRecord;

class CreateFood extends CreateRecord
{
    protected static string $resource = FoodResource::class;

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] ??= null;

        return TranslatableData::clean($data, ['name', 'aliases']);
    }
}
