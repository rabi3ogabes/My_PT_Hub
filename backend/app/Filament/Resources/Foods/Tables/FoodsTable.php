<?php

namespace App\Filament\Resources\Foods\Tables;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\Foods\Schemas\FoodForm;
use App\Filament\Support\JsonSearch;
use App\Filament\Support\LibraryFilters;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FoodsTable
{
    public static function moderate(Food $food, string $status, bool $verify = false): void
    {
        $food->forceFill(['moderation' => $status] + ($status === 'approved' && $verify ? ['is_verified' => true] : []))->save();
        ActivityLogger::log('admin.food.'.$status, null, ['food_id' => $food->id, 'verified' => $verify], tenantId: $food->tenant_id);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('tenant'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->state(fn (Food $record): ?string => $record->translate('name'))
                    ->description(fn (Food $record): ?string => $record->brand)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(fn (Builder $q) => $q
                        ->where('brand', 'like', "%{$search}%")
                        ->orWhere('barcode', $search)
                        ->tap(fn (Builder $q) => JsonSearch::orWhere($q, 'name', $search)))),
                LibraryFilters::ownerColumn(),
                TextColumn::make('kcal')->numeric(decimalPlaces: 0)->sortable(),
                TextColumn::make('macros')->label(__('P / C / F'))
                    ->state(fn (Food $record): string => sprintf('%s / %s / %s', round($record->protein_g, 1), round($record->carbs_g, 1), round($record->fat_g, 1))),
                TextColumn::make('source')->badge()->color('gray'),
                TextColumn::make('moderation')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray',
                }),
                IconColumn::make('is_verified')->label(__('Verified'))->boolean(),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('moderation')->options(FoodForm::MODERATION),
                TernaryFilter::make('is_verified')->label(__('Verified')),
                SelectFilter::make('source')->options(['usda' => 'USDA', 'off' => 'Open Food Facts', 'regional' => 'Regional', 'custom' => 'Custom', 'user' => 'User submitted']),
                LibraryFilters::scope(),
                LibraryFilters::tenant(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Food $record): bool => $record->moderation !== 'approved')
                    ->schema([Toggle::make('verify')->label(__('Mark as verified'))->default(false)])
                    ->action(fn (Food $record, array $data) => self::moderate($record, 'approved', (bool) ($data['verify'] ?? false))),
                Action::make('reject')
                    ->label(__('Reject'))
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Food $record): bool => $record->moderation !== 'rejected')
                    ->action(fn (Food $record) => self::moderate($record, 'rejected')),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')->label(__('Approve'))->icon(Heroicon::OutlinedCheck)->color('success')
                        ->action(fn (Collection $records) => $records->each(fn (Food $f) => self::moderate($f, 'approved')))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('rejectSelected')->label(__('Reject'))->icon(Heroicon::OutlinedXMark)->color('danger')->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Food $f) => self::moderate($f, 'rejected')))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('verifySelected')->label(__('Mark verified'))->icon(Heroicon::OutlinedCheckBadge)
                        ->action(fn (Collection $records) => $records->each(fn (Food $f) => $f->forceFill(['is_verified' => true])->save()))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
