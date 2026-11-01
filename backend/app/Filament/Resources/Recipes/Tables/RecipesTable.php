<?php

namespace App\Filament\Resources\Recipes\Tables;

use App\Domain\Nutrition\Models\Recipe;
use App\Filament\Support\JsonSearch;
use App\Filament\Support\LibraryFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('tenant'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->state(fn (Recipe $record): ?string => $record->translate('name'))
                    ->description(fn (Recipe $record): string => $record->slug)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(fn (Builder $q) => $q
                        ->where('slug', 'like', "%{$search}%")
                        ->tap(fn (Builder $q) => JsonSearch::orWhere($q, 'name', $search)))),
                LibraryFilters::ownerColumn(),
                TextColumn::make('cuisine')->badge()->color('gray')->toggleable(),
                TextColumn::make('kcal')->numeric(decimalPlaces: 0)->sortable(),
                TextColumn::make('total_minutes')->label(__('Time'))
                    ->state(fn (Recipe $record): string => ($record->prep_minutes + $record->cook_minutes).' min'),
                ToggleColumn::make('is_published')->label(__('Published')),
                TextColumn::make('created_at')->dateTime()->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                LibraryFilters::scope(),
                LibraryFilters::tenant(),
                TernaryFilter::make('is_published')->label(__('Published')),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }
}
