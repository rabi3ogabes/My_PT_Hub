<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Filament\Resources\Reviews\ReviewModeration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsTable
{
    public static function configure(Table $table, bool $withCoach = true): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['coachProfile.user', 'client']))
            ->defaultSort('created_at', 'desc')
            ->columns(array_values(array_filter([
                $withCoach ? TextColumn::make('coachProfile.user.name')->label(__('Coach'))->searchable() : null,
                TextColumn::make('client.name')->label(__('Client'))->searchable(),
                TextColumn::make('rating')->formatStateUsing(fn ($state): string => str_repeat('★', (int) $state).str_repeat('☆', 5 - (int) $state))->color('primary')->sortable(),
                TextColumn::make('body')->limit(80)->wrap()->searchable()->placeholder('—'),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'rejected' => 'danger', default => 'warning',
                }),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])))
            ->filters([
                SelectFilter::make('status')->options(['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected')])->default('pending'),
                SelectFilter::make('rating')->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            ])
            ->recordActions([
                ViewAction::make()->schema([
                    TextEntry::make('coachProfile.user.name')->label(__('Coach')),
                    TextEntry::make('client.name')->label(__('Client')),
                    TextEntry::make('rating'),
                    TextEntry::make('body')->placeholder('—'),
                    TextEntry::make('coach_reply')->placeholder('—'),
                    TextEntry::make('status')->badge(),
                ]),
                ReviewModeration::approve(),
                ReviewModeration::reject(),
            ])
            ->toolbarActions([BulkActionGroup::make(ReviewModeration::bulk())]);
    }
}
