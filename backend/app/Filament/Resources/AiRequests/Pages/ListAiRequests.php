<?php

namespace App\Filament\Resources\AiRequests\Pages;

use App\Filament\Resources\AiRequests\AiRequestResource;
use App\Filament\Widgets\AiUsageStats;
use Filament\Resources\Pages\ListRecords;

class ListAiRequests extends ListRecords
{
    protected static string $resource = AiRequestResource::class;

    protected function getHeaderWidgets(): array
    {
        return [AiUsageStats::class];
    }
}
