<?php

namespace App\Filament\Resources\FeatureFlags;

use App\Domain\Tenancy\Models\FeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\EditFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\ListFeatureFlags;
use App\Filament\Resources\FeatureFlags\Schemas\FeatureFlagForm;
use App\Filament\Resources\FeatureFlags\Tables\FeatureFlagsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeatureFlagResource extends AdminResource
{
    protected static ?string $model = FeatureFlag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return Nav::platform();
    }

    public static function form(Schema $schema): Schema
    {
        return FeatureFlagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeatureFlagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureFlags::route('/'),
            'create' => CreateFeatureFlag::route('/create'),
            'edit' => EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
