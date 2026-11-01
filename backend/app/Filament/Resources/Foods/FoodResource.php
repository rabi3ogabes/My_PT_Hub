<?php

namespace App\Filament\Resources\Foods;

use App\Domain\Nutrition\Models\Food;
use App\Filament\Resources\Foods\Pages\CreateFood;
use App\Filament\Resources\Foods\Pages\EditFood;
use App\Filament\Resources\Foods\Pages\ListFoods;
use App\Filament\Resources\Foods\Schemas\FoodForm;
use App\Filament\Resources\Foods\Tables\FoodsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoodResource extends AdminResource
{
    protected static ?string $model = Food::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'foods';

    protected static ?string $navigationLabel = 'Foods';

    public static function getNavigationGroup(): ?string
    {
        return Nav::content();
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Food::query()->withoutGlobalScopes()->whereNull('deleted_at')->where('moderation', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return FoodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FoodsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFoods::route('/'),
            'create' => CreateFood::route('/create'),
            'edit' => EditFood::route('/{record}/edit'),
        ];
    }
}
