<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Recipe'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('slug')->required()->maxLength(255)->alphaDash(),
                        Select::make('tenant_id')->label(__('Owner'))->relationship('tenant', 'name')->searchable()->placeholder(__('Global library')),
                        Toggle::make('is_published')->default(true)->inline(false),
                        TextInput::make('cuisine')->maxLength(24),
                        TextInput::make('prep_minutes')->numeric()->integer()->minValue(0)->default(0)->suffix('min'),
                        TextInput::make('cook_minutes')->numeric()->integer()->minValue(0)->default(0)->suffix('min'),
                        TextInput::make('servings')->numeric()->integer()->minValue(1)->maxValue(255)->default(1),
                        TagsInput::make('tags'),
                        TextInput::make('photo_url')->url()->maxLength(255),
                    ]),
                Section::make(__('Content per language'))
                    ->schema([
                        Locales::tabs('recipe_locales', fn (string $locale): array => [
                            TextInput::make("name.{$locale}")->label(__('Name'))->required($locale === 'en')->maxLength(255),
                            Textarea::make("description.{$locale}")->label(__('Description'))->rows(3),
                            Repeater::make("steps.{$locale}")->label(__('Steps'))
                                ->simple(Textarea::make('step')->rows(2)->required())
                                ->reorderable()->defaultItems(0),
                        ]),
                    ]),
                Section::make(__('Per-serving totals (cached)'))
                    ->description(__('Recomputed by the nutrition module from ingredients; edit only to correct imported data.'))
                    ->columns(4)
                    ->collapsed()
                    ->schema([
                        TextInput::make('kcal')->numeric()->minValue(0),
                        TextInput::make('protein_g')->numeric()->minValue(0)->suffix('g'),
                        TextInput::make('carbs_g')->numeric()->minValue(0)->suffix('g'),
                        TextInput::make('fat_g')->numeric()->minValue(0)->suffix('g'),
                    ]),
            ]);
    }
}
