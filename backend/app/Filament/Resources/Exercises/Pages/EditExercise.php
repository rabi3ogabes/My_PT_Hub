<?php

namespace App\Filament\Resources\Exercises\Pages;

use App\Filament\Resources\Exercises\ExerciseResource;
use App\Filament\Support\TranslatableData;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditExercise extends EditRecord
{
    protected static string $resource = ExerciseResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make(), ForceDeleteAction::make()];
    }

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return TranslatableData::listify($data, ['instructions', 'tips', 'mistakes', 'aliases']);
    }

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TranslatableData::clean($data, ['name', 'instructions', 'tips', 'mistakes', 'aliases']);
    }
}
