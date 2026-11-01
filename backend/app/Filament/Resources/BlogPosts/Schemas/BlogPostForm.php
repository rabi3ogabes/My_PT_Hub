<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Article'))
                            ->schema([
                                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                                    ->afterStateUpdated(function ($set, $get, ?string $state, string $operation): void {
                                        if ($operation === 'create' && blank($get('slug'))) {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),
                                TextInput::make('slug')->required()->maxLength(255)
                                    ->regex('/^[\p{L}\p{N}]+(?:-[\p{L}\p{N}]+)*$/u')
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $get): Unique => $rule->where('locale', $get('locale'))),
                                Textarea::make('excerpt')->maxLength(500)->rows(2),
                                MarkdownEditor::make('body')->required()->columnSpanFull(),
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Publishing'))
                            ->schema([
                                Select::make('locale')->options(Locales::options())->required()->default('en'),
                                DateTimePicker::make('published_at')
                                    ->label(__('Publish at'))
                                    ->helperText(__('Empty = draft. A future date schedules the post.')),
                                TextInput::make('category')->maxLength(32),
                                TextInput::make('cover_url')->url()->maxLength(255),
                                TextInput::make('translation_group')
                                    ->label(__('Translation group'))
                                    ->helperText(__('Shared ULID linking the same article across locales (hreflang).'))
                                    ->maxLength(26),
                            ]),
                        Section::make(__('SEO'))
                            ->schema([
                                TextInput::make('meta_title')->maxLength(255)->helperText(__('≈ 60 characters recommended.')),
                                Textarea::make('meta_description')->maxLength(320)->rows(3)->helperText(__('≈ 155 characters recommended.')),
                                Toggle::make('noindex')->label(__('noindex (hide from search engines)')),
                            ]),
                    ]),
            ]);
    }
}
