<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramForm
{
    public const VISIBILITY = ['private' => 'Private', 'tenant_template' => 'Tenant template', 'public' => 'Public'];

    public const GOALS = ['fat_loss' => 'Fat loss', 'muscle_gain' => 'Muscle gain', 'strength' => 'Strength', 'performance' => 'Performance', 'health' => 'Health'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Program'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('slug')->maxLength(255)->alphaDash(),
                        Select::make('tenant_id')->label(__('Owner'))->relationship('tenant', 'name')->searchable()->placeholder(__('Global library')),
                        Select::make('visibility')->options(self::VISIBILITY)->default('public')->required(),
                        Select::make('goal')->options(self::GOALS),
                        Select::make('level')->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'])->default('intermediate')->required(),
                        TextInput::make('days_per_week')->numeric()->integer()->minValue(1)->maxValue(7)->default(3)->required(),
                        TagsInput::make('equipment'),
                        TagsInput::make('tags'),
                        Toggle::make('is_template')->default(true)->inline(false),
                        TextInput::make('cover_url')->url()->maxLength(255)->columnSpanFull(),
                    ]),
                Section::make(__('Content per language'))
                    ->schema([
                        Locales::tabs('program_locales', fn (string $locale): array => [
                            TextInput::make("name.{$locale}")->label(__('Name'))->required($locale === 'en')->maxLength(255),
                            Textarea::make("description.{$locale}")->label(__('Description'))->rows(4),
                        ]),
                    ]),
            ]);
    }
}
