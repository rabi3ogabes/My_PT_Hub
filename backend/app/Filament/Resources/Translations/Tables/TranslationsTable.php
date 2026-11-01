<?php

namespace App\Filament\Resources\Translations\Tables;

use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Tenancy\Models\Translation;
use App\Filament\Support\Locales;
use App\Filament\Support\TranslationSync;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TranslationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('locale')->badge()->sortable(),
                TextColumn::make('group')->badge()->color('gray')->sortable()->searchable(),
                TextColumn::make('key')->searchable()->sortable()->fontFamily('mono')->wrap(),
                TextInputColumn::make('value')->searchable()
                    ->rules(['required', 'string'])
                    ->extraInputAttributes(fn (Translation $record): array => ['dir' => $record->locale === 'ar' ? 'rtl' : 'ltr']),
                TextColumn::make('updated_at')->dateTime()->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')->options(Locales::options()),
                SelectFilter::make('group')->options(fn (): array => Translation::query()->distinct()->orderBy('group')->pluck('group', 'group')->all())->searchable(),
            ])
            ->headerActions([
                Action::make('syncFromLangFiles')
                    ->label(__('Sync from lang files'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->schema([
                        Toggle::make('overwrite')->label(__('Overwrite values edited in the database'))->default(false),
                    ])
                    ->action(function (array $data): void {
                        $result = app(TranslationSync::class)->sync((bool) ($data['overwrite'] ?? false));
                        ActivityLogger::log('admin.translations.synced', null, $result);
                        Notification::make()
                            ->title(__('Translations synced'))
                            ->body(__(':created created, :updated updated', $result))
                            ->success()->send();
                    }),
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
