<?php

namespace App\Filament\Resources\Exercises\Tables;

use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Workouts\Models\Exercise;
use App\Filament\Support\JsonSearch;
use App\Filament\Support\LibraryFilters;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ExercisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('tenant'))
            ->defaultSort('slug')
            ->columns([
                TextColumn::make('name')
                    ->state(fn (Exercise $record): ?string => $record->translate('name'))
                    ->description(fn (Exercise $record): string => $record->slug)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(fn (Builder $q) => $q
                        ->where('slug', 'like', "%{$search}%")
                        ->tap(fn (Builder $q) => JsonSearch::orWhere($q, 'name', $search)))),
                LibraryFilters::ownerColumn(),
                TextColumn::make('primary_muscle')->badge()->color('gray')->sortable(),
                TextColumn::make('equipment')->badge()->color('gray')->toggleable(),
                TextColumn::make('level')->badge()->sortable(),
                TextColumn::make('locales')->label(__('Locales'))
                    ->state(fn (Exercise $record): string => implode(' ', array_map('strtoupper', array_keys(array_filter($record->name ?? []))))),
                IconColumn::make('video_url')->label(__('Video'))->boolean()
                    ->state(fn (Exercise $record): bool => filled($record->video_url)),
                ToggleColumn::make('is_published')->label(__('Published')),
                TextColumn::make('updated_at')->dateTime()->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                LibraryFilters::scope(),
                LibraryFilters::tenant(),
                SelectFilter::make('primary_muscle')->options(fn (): array => Exercise::query()->withoutGlobalScopes()->whereNotNull('primary_muscle')->distinct()->orderBy('primary_muscle')->pluck('primary_muscle', 'primary_muscle')->all()),
                SelectFilter::make('level')->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']),
                TernaryFilter::make('is_published')->label(__('Published')),
                TernaryFilter::make('has_video')->label(__('Has video'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('video_url'),
                        false: fn (Builder $query): Builder => $query->whereNull('video_url'),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')->label(__('Publish'))->icon(Heroicon::OutlinedEye)
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_published' => true]);
                            ActivityLogger::log('admin.exercises.published', null, ['count' => $records->count()]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')->label(__('Unpublish'))->icon(Heroicon::OutlinedEyeSlash)
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_published' => false]);
                            ActivityLogger::log('admin.exercises.unpublished', null, ['count' => $records->count()]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
