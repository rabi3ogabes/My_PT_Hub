<?php

namespace App\Filament\Resources\CoachProfiles\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoachProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Marketplace status'))
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_published')->label(__('Published')),
                        Toggle::make('is_verified')->label(__('Verified badge')),
                        Toggle::make('is_featured')->label(__('Featured')),
                    ]),
                Section::make(__('Profile'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')->required()->maxLength(255)->alphaDash()->unique(ignoreRecord: true),
                        TextInput::make('headline')->maxLength(255),
                        TextInput::make('city')->maxLength(255),
                        TextInput::make('country')->length(2),
                        TagsInput::make('specialties'),
                        TagsInput::make('languages'),
                        TextInput::make('instagram')->maxLength(255),
                        TextInput::make('intro_video_url')->url()->maxLength(255),
                    ]),
                Section::make(__('Bio'))
                    ->schema([
                        Locales::tabs('bio_locales', fn (string $locale): array => [
                            Textarea::make("bio.{$locale}")->label(__('Bio'))->rows(6),
                        ]),
                    ]),
            ]);
    }
}
