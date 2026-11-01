<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Tenant;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Business'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('slug')->copyable(),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('plan')->badge()->color('primary'),
                        TextEntry::make('locale')->badge(),
                        TextEntry::make('currency'),
                        TextEntry::make('timezone'),
                        TextEntry::make('country')->placeholder('—'),
                        TextEntry::make('brand_color')->color('primary'),
                        TextEntry::make('trial_ends_at')->dateTime()->placeholder('—'),
                        TextEntry::make('onboarded_at')->dateTime()->placeholder('—'),
                        TextEntry::make('created_at')->dateTime(),
                    ]),
                Section::make(__('Status'))
                    ->columns(3)
                    ->schema([
                        IconEntry::make('suspended')
                            ->label(__('Suspended'))
                            ->boolean()
                            ->state(fn (Tenant $record): bool => filled($record->setting('suspended_at'))),
                        TextEntry::make('suspended_reason')
                            ->label(__('Suspension reason'))
                            ->state(fn (Tenant $record): ?string => $record->setting('suspended_reason'))
                            ->placeholder('—'),
                        TextEntry::make('staff')
                            ->label(__('Staff / clients'))
                            ->state(fn (Tenant $record): string => $record->staffCount().' / '.$record->clientCount()),
                    ]),
                Section::make(__('Current subscription'))
                    ->columns(4)
                    ->schema([
                        TextEntry::make('subscription.plan.code')->label(__('Plan'))->badge()->placeholder('—'),
                        TextEntry::make('subscription.status')->label(__('Status'))->badge()->placeholder('—'),
                        TextEntry::make('subscription.interval')->label(__('Interval'))->placeholder('—'),
                        TextEntry::make('subscription.price_minor')
                            ->label(__('Price'))
                            ->formatStateUsing(fn ($state, Tenant $record): string => Money::format((int) $state, (string) $record->subscription?->currency))
                            ->placeholder('—'),
                        TextEntry::make('subscription.current_period_end')->label(__('Period ends'))->dateTime()->placeholder('—'),
                        IconEntry::make('subscription.cancel_at_period_end')->label(__('Cancels at period end'))->boolean(),
                        TextEntry::make('subscription.gateway')->label(__('Gateway'))->placeholder('—'),
                    ]),
            ]);
    }
}
