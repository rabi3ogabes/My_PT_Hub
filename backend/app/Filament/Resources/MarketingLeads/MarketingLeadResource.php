<?php

namespace App\Filament\Resources\MarketingLeads;

use App\Domain\Marketing\Models\MarketingLead;
use App\Filament\Resources\MarketingLeads\Pages\ListMarketingLeads;
use App\Filament\Resources\MarketingLeads\Tables\MarketingLeadsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MarketingLeadResource extends AdminResource
{
    protected static ?string $model = MarketingLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'email';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return Nav::marketing();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return MarketingLeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListMarketingLeads::route('/')];
    }
}
