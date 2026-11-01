<?php

namespace App\Filament\Resources\Recipes\Pages;

use App\Filament\Resources\Recipes\RecipeResource;
use App\Filament\Support\TranslatableData;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] ??= null;

        return TranslatableData::clean($data, ['name', 'description', 'steps']);
    }
}
