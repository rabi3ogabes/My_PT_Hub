<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Domain\Workouts\Models\Program;
use App\Filament\Resources\Programs\Schemas\ProgramForm;
use App\Filament\Support\JsonSearch;
use App\Filament\Support\LibraryFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('tenant')->withCount(['weeks', 'assignments']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->state(fn (Program $record): ?string => $record->translate('name'))
                    ->description(fn (Program $record): ?string => $record->slug)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(fn (Builder $q) => $q
                        ->where('slug', 'like', "%{$search}%")
                        ->tap(fn (Builder $q) => JsonSearch::orWhere($q, 'name', $search)))),
                LibraryFilters::ownerColumn(),
                TextColumn::make('visibility')->badge()->color(fn (string $state): string => $state === 'public' ? 'success' : 'gray'),
                TextColumn::make('goal')->badge()->color('gray')->placeholder('—'),
                TextColumn::make('level')->badge(),
                TextColumn::make('days_per_week')->label(__('Days/wk'))->numeric(),
                TextColumn::make('weeks_count')->label(__('Weeks'))->numeric()->sortable(),
                TextColumn::make('assignments_count')->label(__('Assigned'))->numeric()->sortable(),
                IconColumn::make('is_template')->label(__('Template'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('visibility')->options(ProgramForm::VISIBILITY),
                SelectFilter::make('goal')->options(ProgramForm::GOALS),
                TernaryFilter::make('is_template')->label(__('Template')),
                LibraryFilters::scope(),
                LibraryFilters::tenant(),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }
}
