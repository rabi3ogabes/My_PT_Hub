<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\Identity\Models\User;
use App\Filament\Support\Locales;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Account'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('phone')->tel()->maxLength(32),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->minLength(12)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(__('Leave empty to keep the current password.')),
                        DateTimePicker::make('email_verified_at')->label(__('Email verified at')),
                    ]),
                Section::make(__('Preferences'))
                    ->columns(3)
                    ->schema([
                        Select::make('locale')->options(Locales::options())->required()->default('en'),
                        Select::make('timezone')->options(fn (): array => array_combine(timezone_identifiers_list(), timezone_identifiers_list()))->searchable()->required()->default('UTC'),
                        Select::make('units')->options(['metric' => __('Metric'), 'imperial' => __('Imperial')])->required()->default('metric'),
                    ]),
                Section::make(__('Platform access'))
                    ->schema([
                        Toggle::make('is_super_admin')
                            ->label(__('Super admin'))
                            ->helperText(__('Grants full access to this admin panel.'))
                            ->disabled(fn (?User $record): bool => $record !== null && $record->is(auth()->user())),
                    ]),
            ]);
    }
}
