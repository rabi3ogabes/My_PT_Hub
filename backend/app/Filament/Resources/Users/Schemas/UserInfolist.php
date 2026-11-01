<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\Identity\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Account'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('phone')->placeholder('—'),
                        IconEntry::make('is_super_admin')->label(__('Super admin'))->boolean(),
                        IconEntry::make('two_factor')->label(__('2FA enabled'))->boolean()
                            ->state(fn (User $record): bool => $record->hasTwoFactorEnabled()),
                        TextEntry::make('email_verified_at')->dateTime()->placeholder(__('Not verified')),
                        TextEntry::make('currentTenant.name')->label(__('Current tenant'))->placeholder('—'),
                        TextEntry::make('locale')->badge(),
                        TextEntry::make('timezone'),
                        TextEntry::make('last_login_at')->dateTime()->placeholder('—'),
                        TextEntry::make('last_active_at')->dateTime()->placeholder('—'),
                        TextEntry::make('locked_until')->dateTime()->placeholder('—'),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('deleted_at')->dateTime()->placeholder('—')->visible(fn (User $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
