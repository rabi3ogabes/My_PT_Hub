<?php

namespace App\Filament\Resources\CoachProfiles\Pages;

use App\Filament\Resources\CoachProfiles\CoachProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListCoachProfiles extends ListRecords
{
    protected static string $resource = CoachProfileResource::class;
}
