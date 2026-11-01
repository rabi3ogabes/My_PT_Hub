<?php

namespace App\Filament\Resources\CoachProfiles\RelationManagers;

use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    public static function shouldSkipAuthorization(): bool
    {
        return auth()->user()?->is_super_admin === true;
    }

    public function table(Table $table): Table
    {
        return ReviewsTable::configure($table, withCoach: false);
    }
}
