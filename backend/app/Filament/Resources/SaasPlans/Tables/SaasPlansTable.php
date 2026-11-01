<?php

namespace App\Filament\Resources\SaasPlans\Tables;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Tenant;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SaasPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('code')->badge()->color('primary')->searchable(),
                TextColumn::make('name')->formatStateUsing(fn ($state, SaasPlan $record): string => (string) $record->translate('name')),
                TextColumn::make('monthly_price_minor')->label(__('Monthly'))
                    ->formatStateUsing(fn ($state, SaasPlan $record): string => Money::format((int) $state, $record->currency)),
                TextColumn::make('annual_price_minor')->label(__('Annual'))
                    ->formatStateUsing(fn ($state, SaasPlan $record): string => Money::format((int) $state, $record->currency)),
                TextColumn::make('limits.clients')->label(__('Clients'))->placeholder('∞'),
                TextColumn::make('limits.coaches')->label(__('Coaches'))->placeholder('∞'),
                TextColumn::make('features')->label(__('Features'))
                    ->state(fn (SaasPlan $record): int => count($record->features ?? []))
                    ->tooltip(fn (SaasPlan $record): string => implode(', ', $record->features ?? [])),
                TextColumn::make('tenants')->label(__('Tenants'))
                    ->state(fn (SaasPlan $record): int => Tenant::query()->where('plan', $record->code)->count()),
                TextColumn::make('trial_days')->label(__('Trial')),
                IconColumn::make('is_public')->boolean()->label(__('Public')),
            ])
            ->filters([
                TernaryFilter::make('is_public'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
