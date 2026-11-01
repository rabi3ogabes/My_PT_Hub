<?php

namespace App\Filament\Resources\SaasSubscriptions;

use App\Domain\Billing\Models\SaasSubscription;
use App\Filament\Resources\SaasSubscriptions\Pages\ListSaasSubscriptions;
use App\Filament\Resources\SaasSubscriptions\Pages\ViewSaasSubscription;
use App\Filament\Resources\SaasSubscriptions\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\SaasSubscriptions\Schemas\SaasSubscriptionInfolist;
use App\Filament\Resources\SaasSubscriptions\Tables\SaasSubscriptionsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SaasSubscriptionResource extends AdminResource
{
    protected static ?string $model = SaasSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'SaaS subscription';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return Nav::billing();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return SaasSubscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaasSubscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [InvoicesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaasSubscriptions::route('/'),
            'view' => ViewSaasSubscription::route('/{record}'),
        ];
    }
}
