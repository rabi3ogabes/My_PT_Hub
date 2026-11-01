<?php

namespace App\Filament\Resources\Tenants\RelationManagers;

use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Shared\Support\Money;
use App\Filament\Resources\SaasSubscriptions\SaasSubscriptionResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'Subscriptions';

    public static function shouldSkipAuthorization(): bool
    {
        return auth()->user()?->is_super_admin === true;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('plan'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('plan.code')->label(__('Plan'))->badge()->color('primary'),
                TextColumn::make('status')->badge(),
                TextColumn::make('interval'),
                TextColumn::make('price_minor')->label(__('Price'))
                    ->formatStateUsing(fn ($state, SaasSubscription $record): string => Money::format((int) $state, $record->currency)),
                TextColumn::make('current_period_end')->dateTime()->label(__('Period end')),
                IconColumn::make('cancel_at_period_end')->boolean()->label(__('Cancelling')),
                TextColumn::make('created_at')->dateTime()->since(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (SaasSubscription $record): string => SaasSubscriptionResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
