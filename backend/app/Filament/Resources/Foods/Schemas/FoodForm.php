<?php

namespace App\Filament\Resources\Foods\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FoodForm
{
    public const MODERATION = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    public static function configure(Schema $schema): Schema
    {
        $macro = fn (string $field, string $label, string $suffix = 'g'): TextInput => TextInput::make($field)->label($label)->numeric()->minValue(0)->suffix($suffix)->default(0)->required();

        return $schema
            ->components([
                Section::make(__('Food'))
                    ->columns(3)
                    ->schema([
                        Select::make('tenant_id')->label(__('Owner'))->relationship('tenant', 'name')->searchable()->placeholder(__('Global library')),
                        TextInput::make('brand')->maxLength(255),
                        TextInput::make('barcode')->maxLength(32),
                        Select::make('source')->options(['usda' => 'USDA', 'off' => 'Open Food Facts', 'regional' => 'Regional', 'custom' => 'Custom', 'user' => 'User submitted'])->default('custom')->required(),
                        Select::make('region')->options(['gulf' => 'Gulf', 'levant' => 'Levant', 'maghreb' => 'Maghreb', 'french' => 'French', 'international' => 'International']),
                        Select::make('category')->options(collect(['produce', 'meat', 'dairy', 'bakery', 'grains', 'pantry', 'drinks', 'frozen', 'snacks', 'prepared'])->mapWithKeys(fn (string $c): array => [$c => ucfirst($c)])->all()),
                        Select::make('moderation')->options(self::MODERATION)->default('approved')->required(),
                        Toggle::make('is_verified')->label(__('Verified'))->inline(false),
                    ]),
                Section::make(__('Name per language'))
                    ->schema([
                        Locales::tabs('food_locales', fn (string $locale): array => [
                            TextInput::make("name.{$locale}")->label(__('Name'))->required($locale === 'en')->maxLength(255),
                            TagsInput::make("aliases.{$locale}")->label(__('Aliases')),
                        ]),
                    ]),
                Section::make(__('Nutrition per 100 g'))
                    ->columns(4)
                    ->schema([
                        $macro('kcal', 'kcal', 'kcal'),
                        $macro('protein_g', __('Protein')),
                        $macro('carbs_g', __('Carbs')),
                        $macro('fat_g', __('Fat')),
                        $macro('fiber_g', __('Fiber')),
                        $macro('sugar_g', __('Sugar')),
                        $macro('sodium_mg', __('Sodium'), 'mg'),
                        KeyValue::make('micros')->keyLabel(__('Micronutrient'))->valueLabel(__('Amount'))->columnSpanFull(),
                    ]),
                Section::make(__('Servings & tags'))
                    ->columns(2)
                    ->schema([
                        Repeater::make('servings')
                            ->schema([
                                TextInput::make('unit')->required()->maxLength(32),
                                TextInput::make('grams')->numeric()->minValue(0.1)->required(),
                                TextInput::make('label.en')->label(__('Label (EN)')),
                            ])
                            ->columns(3)->defaultItems(0)->columnSpanFull(),
                        TagsInput::make('diet_tags')->suggestions(['halal', 'vegan', 'vegetarian', 'gluten_free', 'dairy_free', 'keto']),
                        TagsInput::make('allergens')->suggestions(['gluten', 'dairy', 'eggs', 'nuts', 'peanuts', 'soy', 'fish', 'shellfish', 'sesame']),
                    ]),
            ]);
    }
}
