<?php

namespace App\Filament\Resources\SaasPlans;

use App\Domain\Billing\Models\SaasPlan;
use App\Filament\Resources\SaasPlans\Pages\CreateSaasPlan;
use App\Filament\Resources\SaasPlans\Pages\EditSaasPlan;
use App\Filament\Resources\SaasPlans\Pages\ListSaasPlans;
use App\Filament\Resources\SaasPlans\Schemas\SaasPlanForm;
use App\Filament\Resources\SaasPlans\Tables\SaasPlansTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SaasPlanResource extends AdminResource
{
    protected static ?string $model = SaasPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $modelLabel = 'SaaS plan';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return Nav::billing();
    }

    public static function form(Schema $schema): Schema
    {
        return SaasPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaasPlansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaasPlans::route('/'),
            'create' => CreateSaasPlan::route('/create'),
            'edit' => EditSaasPlan::route('/{record}/edit'),
        ];
    }
}
