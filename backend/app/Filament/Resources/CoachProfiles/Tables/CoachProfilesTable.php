<?php

namespace App\Filament\Resources\CoachProfiles\Tables;

use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Tenancy\Models\CoachProfile;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachProfilesTable
{
    private static function flagToggle(string $column, string $label): ToggleColumn
    {
        return ToggleColumn::make($column)->label($label)
            ->afterStateUpdated(fn (CoachProfile $record, bool $state) => ActivityLogger::log("admin.coach_profile.{$column}", null, ['coach_profile_id' => $record->id, 'value' => $state], tenantId: $record->tenant_id));
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['user', 'tenant']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label(__('Coach'))->searchable()->weight('semibold')
                    ->description(fn (CoachProfile $record): string => '/coaches/'.$record->slug),
                TextColumn::make('tenant.name')->label(__('Tenant'))->searchable()->toggleable(),
                TextColumn::make('headline')->limit(40)->toggleable(),
                TextColumn::make('city')->searchable()
                    ->description(fn (CoachProfile $record): ?string => $record->country),
                TextColumn::make('rating_avg')->label(__('Rating'))
                    ->formatStateUsing(fn ($state, CoachProfile $record): string => number_format((float) $state, 1).' ('.$record->rating_count.')')
                    ->sortable(),
                self::flagToggle('is_published', __('Published')),
                self::flagToggle('is_verified', __('Verified')),
                self::flagToggle('is_featured', __('Featured')),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label(__('Published')),
                TernaryFilter::make('is_verified')->label(__('Verified')),
                TernaryFilter::make('is_featured')->label(__('Featured')),
                SelectFilter::make('country')->options(fn (): array => CoachProfile::query()->withoutGlobalScopes()->whereNotNull('country')->distinct()->orderBy('country')->pluck('country', 'country')->all()),
            ])
            ->recordActions([EditAction::make()]);
    }
}
