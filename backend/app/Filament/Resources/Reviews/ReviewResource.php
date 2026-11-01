<?php

namespace App\Filament\Resources\Reviews;

use App\Domain\Marketing\Models\Review;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReviewResource extends AdminResource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return Nav::marketing();
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Review::query()->withoutGlobalScopes()->where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListReviews::route('/')];
    }
}
