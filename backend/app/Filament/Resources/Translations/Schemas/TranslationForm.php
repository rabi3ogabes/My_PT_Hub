<?php

namespace App\Filament\Resources\Translations\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class TranslationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('locale')->options(Locales::options())->required(),
                TextInput::make('group')->required()->maxLength(64)->default('*')
                    ->helperText(__('Lang file name (e.g. billing) or * for JSON strings.')),
                TextInput::make('key')->required()->maxLength(255)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $get): Unique => $rule->where('locale', $get('locale'))->where('group', $get('group'))),
                Textarea::make('value')->required()->rows(4)->columnSpanFull(),
            ]);
    }
}
