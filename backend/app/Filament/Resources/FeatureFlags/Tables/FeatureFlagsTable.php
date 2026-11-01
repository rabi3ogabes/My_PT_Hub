<?php

namespace App\Filament\Resources\FeatureFlags\Tables;

use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Tenancy\Models\FeatureFlag;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeatureFlagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('key')->searchable()->sortable()->weight('semibold')->fontFamily('mono')
                    ->description(fn (FeatureFlag $record): ?string => $record->description),
                ToggleColumn::make('enabled')
                    ->afterStateUpdated(fn (FeatureFlag $record, bool $state) => ActivityLogger::log('admin.feature_flag.toggled', null, ['key' => $record->key, 'enabled' => $state])),
                TextColumn::make('plans')->badge()->placeholder(__('All plans')),
                TextColumn::make('tenants')->label(__('Allow-list'))
                    ->state(fn (FeatureFlag $record): int => count($record->tenants ?? [])),
                TextColumn::make('rollout')->suffix('%')->sortable(),
                TextColumn::make('updated_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('enabled'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->after(fn (FeatureFlag $record) => ActivityLogger::log('admin.feature_flag.deleted', null, ['key' => $record->key])),
            ]);
    }
}
