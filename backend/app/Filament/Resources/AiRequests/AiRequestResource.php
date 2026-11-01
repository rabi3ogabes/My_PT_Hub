<?php

namespace App\Filament\Resources\AiRequests;

use App\Domain\Ai\Models\AiRequest;
use App\Filament\Resources\AiRequests\Pages\ListAiRequests;
use App\Filament\Resources\AiRequests\Tables\AiRequestsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AiRequestResource extends AdminResource
{
    protected static ?string $model = AiRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'AI usage';

    protected static ?string $modelLabel = 'AI request';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return Nav::operations();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AiRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListAiRequests::route('/')];
    }
}
