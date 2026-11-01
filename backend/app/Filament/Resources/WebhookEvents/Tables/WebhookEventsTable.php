<?php

namespace App\Filament\Resources\WebhookEvents\Tables;

use App\Domain\Payments\Models\WebhookEvent;
use App\Filament\Resources\WebhookEvents\ReplayWebhookAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WebhookEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('driver')->badge()->sortable(),
                TextColumn::make('type')->searchable()->placeholder('—'),
                TextColumn::make('external_id')->searchable()->fontFamily('mono')->limit(28)->copyable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'processed' => 'success', 'failed' => 'danger', 'ignored' => 'gray', default => 'info',
                }),
                TextColumn::make('error')->limit(50)->color('danger')->placeholder('—')->toggleable(),
                TextColumn::make('processed_at')->dateTime()->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('driver')->options(fn (): array => WebhookEvent::query()->distinct()->orderBy('driver')->pluck('driver', 'driver')->all()),
                SelectFilter::make('status')->options(['received' => 'Received', 'processed' => 'Processed', 'failed' => 'Failed', 'ignored' => 'Ignored']),
                SelectFilter::make('type')->options(fn (): array => WebhookEvent::query()->whereNotNull('type')->distinct()->orderBy('type')->pluck('type', 'type')->all())->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                ReplayWebhookAction::make(),
            ]);
    }
}
