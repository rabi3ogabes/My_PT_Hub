<?php

namespace App\Filament\Support;

use App\Domain\Tenancy\Models\Tenant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Shared table pieces for library content (global rows + tenant-private rows). */
final class LibraryFilters
{
    public static function scope(): TernaryFilter
    {
        return TernaryFilter::make('global')
            ->label(__('Library'))
            ->placeholder(__('All'))
            ->trueLabel(__('Global library'))
            ->falseLabel(__('Tenant private'))
            ->queries(
                true: fn (Builder $query): Builder => $query->whereNull($query->qualifyColumn('tenant_id')),
                false: fn (Builder $query): Builder => $query->whereNotNull($query->qualifyColumn('tenant_id')),
            );
    }

    public static function tenant(): SelectFilter
    {
        return SelectFilter::make('tenant_id')
            ->label(__('Tenant'))
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Tenant::query()->where('name', 'like', "%{$search}%")->limit(25)->pluck('name', 'id')->all())
            ->getOptionLabelUsing(fn ($value): ?string => Tenant::query()->find($value)?->name)
            ->options([]);
    }

    public static function ownerColumn(): TextColumn
    {
        return TextColumn::make('tenant.name')
            ->label(__('Owner'))
            ->badge()
            ->state(fn (Model $record): string => $record->getAttribute('tenant_id') === null ? __('Global') : (string) $record->getRelationValue('tenant')?->name)
            ->color(fn (Model $record): string => $record->getAttribute('tenant_id') === null ? 'primary' : 'gray');
    }
}
