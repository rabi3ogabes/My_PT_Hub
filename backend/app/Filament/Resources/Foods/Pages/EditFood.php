<?php

namespace App\Filament\Resources\Foods\Pages;

use App\Filament\Resources\Foods\FoodResource;
use App\Filament\Support\TranslatableData;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFood extends EditRecord
{
    protected static string $resource = FoodResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return TranslatableData::listify($data, ['aliases']);
    }

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TranslatableData::clean($data, ['name', 'aliases']);
    }
}
