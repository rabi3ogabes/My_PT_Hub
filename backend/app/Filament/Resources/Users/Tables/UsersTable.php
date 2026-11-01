<?php

namespace App\Filament\Resources\Users\Tables;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Actions\ImpersonateAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('currentTenant')->withCount('memberships'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('semibold')
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('email')->searchable()->copyable()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_super_admin')->label(__('Admin'))->boolean()->trueColor('primary'),
                TextColumn::make('currentTenant.name')->label(__('Current tenant'))->placeholder('—')->toggleable(),
                TextColumn::make('memberships_count')->label(__('Tenants'))->numeric()->sortable(),
                IconColumn::make('email_verified_at')->label(__('Verified'))->boolean()
                    ->state(fn (User $record): bool => $record->email_verified_at !== null),
                TextColumn::make('locale')->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_login_at')->dateTime()->since()->sortable()->placeholder('—'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_super_admin')->label(__('Super admin')),
                TernaryFilter::make('email_verified_at')->label(__('Email verified'))->nullable(),
                SelectFilter::make('role')
                    ->label(__('Role in any tenant'))
                    ->options(collect(User::ROLES)->mapWithKeys(fn (string $r): array => [$r => ucfirst($r)])->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('memberships', fn (Builder $q) => $q->where('role', $data['value']))
                        : $query),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    ImpersonateAction::make(),
                    Action::make('unlock')
                        ->label(__('Unlock account'))
                        ->icon(Heroicon::OutlinedLockOpen)
                        ->visible(fn (User $record): bool => $record->locked_until !== null || $record->failed_logins > 0)
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $record->forceFill(['locked_until' => null, 'failed_logins' => 0])->save();
                            ActivityLogger::log('admin.user.unlocked', $record);
                            Notification::make()->title(__('Account unlocked'))->success()->send();
                        }),
                    RestoreAction::make(),
                ]),
            ]);
    }
}
