<?php

namespace App\Filament\Resources\SaasSubscriptions\Schemas;

use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Shared\Support\Money;
use App\Filament\Resources\SaasSubscriptions\Tables\SaasSubscriptionsTable;
use App\Filament\Resources\Tenants\TenantResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaasSubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Subscription'))
                    ->columns(4)
                    ->schema([
                        TextEntry::make('tenant.name')->label(__('Tenant'))
                            ->url(fn (SaasSubscription $record): string => TenantResource::getUrl('view', ['record' => $record->tenant_id])),
                        TextEntry::make('plan.code')->label(__('Plan'))->badge()->color('primary'),
                        TextEntry::make('status')->badge()->color(fn (?string $state): string => SaasSubscriptionsTable::statusColor($state)),
                        TextEntry::make('interval'),
                        TextEntry::make('price_minor')->label(__('Price'))
                            ->formatStateUsing(fn ($state, SaasSubscription $record): string => Money::format((int) $state, $record->currency)),
                        TextEntry::make('trial_ends_at')->dateTime()->placeholder('—'),
                        TextEntry::make('current_period_start')->dateTime()->placeholder('—'),
                        TextEntry::make('current_period_end')->dateTime()->placeholder('—'),
                        IconEntry::make('cancel_at_period_end')->boolean(),
                        TextEntry::make('cancelled_at')->dateTime()->placeholder('—'),
                        TextEntry::make('cancel_reason')->placeholder('—'),
                        TextEntry::make('failed_attempts'),
                    ]),
                Section::make(__('Gateway'))
                    ->columns(4)
                    ->schema([
                        TextEntry::make('gateway')->badge(),
                        TextEntry::make('gateway_customer_id')->copyable()->placeholder('—'),
                        TextEntry::make('gateway_subscription_id')->copyable()->placeholder('—'),
                        TextEntry::make('card_last4')->label(__('Card'))
                            ->formatStateUsing(fn ($state, SaasSubscription $record): string => trim(($record->card_brand ?? '').' •••• '.$state))
                            ->placeholder('—'),
                        TextEntry::make('reminder_7d_sent_at')->dateTime()->placeholder('—'),
                        TextEntry::make('reminder_1d_sent_at')->dateTime()->placeholder('—'),
                    ]),
            ]);
    }
}
