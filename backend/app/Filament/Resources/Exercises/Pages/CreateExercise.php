<?php

namespace App\Filament\Resources\Exercises\Pages;

use App\Filament\Resources\Exercises\ExerciseResource;
use App\Filament\Support\TranslatableData;
use Filament\Resources\Pages\CreateRecord;

class CreateExercise extends CreateRecord
{
    protected static string $resource = ExerciseResource::class;

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] ??= null;

        return TranslatableData::clean($data, ['name', 'instructions', 'tips', 'mistakes', 'aliases']);
    }
}
