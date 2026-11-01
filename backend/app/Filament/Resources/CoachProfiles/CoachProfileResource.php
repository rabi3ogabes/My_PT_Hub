<?php

namespace App\Filament\Resources\CoachProfiles;

use App\Domain\Tenancy\Models\CoachProfile;
use App\Filament\Resources\CoachProfiles\Pages\EditCoachProfile;
use App\Filament\Resources\CoachProfiles\Pages\ListCoachProfiles;
use App\Filament\Resources\CoachProfiles\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\CoachProfiles\Schemas\CoachProfileForm;
use App\Filament\Resources\CoachProfiles\Tables\CoachProfilesTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CoachProfileResource extends AdminResource
{
    protected static ?string $model = CoachProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return Nav::marketing();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CoachProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoachProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [ReviewsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoachProfiles::route('/'),
            'edit' => EditCoachProfile::route('/{record}/edit'),
        ];
    }
}
