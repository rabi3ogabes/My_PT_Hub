<?php

namespace App\Filament\Resources\Translations;

use App\Domain\Tenancy\Models\Translation;
use App\Filament\Resources\Translations\Pages\ListTranslations;
use App\Filament\Resources\Translations\Schemas\TranslationForm;
use App\Filament\Resources\Translations\Tables\TranslationsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TranslationResource extends AdminResource
{
    protected static ?string $model = Translation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return Nav::platform();
    }

    public static function form(Schema $schema): Schema
    {
        return TranslationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TranslationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTranslations::route('/'),
        ];
    }
}
