<?php

namespace App\Filament\Resources\SaasSubscriptions\Tables;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Shared\Support\Money;
use App\Filament\Resources\SaasSubscriptions\Actions\SubscriptionActions;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SaasSubscriptionsTable
{
    public const STATUSES = ['trialing' => 'Trialing', 'active' => 'Active', 'past_due' => 'Past due', 'cancelled' => 'Cancelled', 'expired' => 'Expired'];

    public static function statusColor(?string $state): string
    {
        return match ($state) {
            'active' => 'success', 'trialing' => 'info', 'past_due' => 'warning', 'cancelled', 'expired' => 'gray', default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['tenant', 'plan']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('tenant.name')->label(__('Tenant'))->searchable()->sortable(),
                TextColumn::make('plan.code')->label(__('Plan'))->badge()->color('primary'),
                TextColumn::make('status')->badge()->color(fn (?string $state): string => self::statusColor($state))->sortable(),
                TextColumn::make('interval')->sortable(),
                TextColumn::make('price_minor')->label(__('Price'))->sortable()
                    ->formatStateUsing(fn ($state, SaasSubscription $record): string => Money::format((int) $state, $record->currency)),
                TextColumn::make('current_period_end')->label(__('Period end'))->dateTime()->sortable(),
                IconColumn::make('cancel_at_period_end')->label(__('Cancelling'))->boolean(),
                TextColumn::make('gateway')->badge()->toggleable(),
                TextColumn::make('card_last4')->label(__('Card'))
                    ->formatStateUsing(fn ($state, SaasSubscription $record): string => trim(($record->card_brand ?? '').' •••• '.$state))
                    ->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('failed_attempts')->label(__('Failed'))->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::STATUSES)->multiple(),
                SelectFilter::make('saas_plan_id')->label(__('Plan'))->options(fn (): array => SaasPlan::query()->orderBy('sort')->pluck('code', 'id')->all()),
                SelectFilter::make('interval')->options(['month' => __('Monthly'), 'year' => __('Annual')]),
                SelectFilter::make('gateway')->options(fn (): array => SaasSubscription::query()->distinct()->pluck('gateway', 'gateway')->all()),
                TernaryFilter::make('cancel_at_period_end')->label(__('Cancelling at period end')),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    SubscriptionActions::cancel(),
                    SubscriptionActions::resume(),
                    SubscriptionActions::refund(),
                ]),
            ]);
    }
}
