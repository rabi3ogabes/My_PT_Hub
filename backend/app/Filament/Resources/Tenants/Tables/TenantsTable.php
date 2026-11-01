<?php

namespace App\Filament\Resources\Tenants\Tables;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Tenancy\Models\Tenant;
use App\Filament\Resources\Tenants\Actions\TenantActions;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('memberships')->with('subscription'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('semibold')
                    ->description(fn (Tenant $record): string => $record->slug),
                TextColumn::make('type')->badge()->toggleable(),
                TextColumn::make('plan')->badge()->color('primary')->sortable(),
                TextColumn::make('subscription.status')->label(__('Subscription'))->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success', 'trialing' => 'info', 'past_due' => 'warning', default => 'gray',
                    })->placeholder('—'),
                TextColumn::make('memberships_count')->label(__('Members'))->sortable()->numeric(),
                IconColumn::make('is_suspended')->label(__('Suspended'))->boolean()
                    ->state(fn (Tenant $record): bool => filled($record->setting('suspended_at')))
                    ->trueColor('danger')->falseColor('success')
                    ->trueIcon('heroicon-o-no-symbol')->falseIcon('heroicon-o-check-circle'),
                TextColumn::make('locale')->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->since(),
            ])
            ->filters([
                SelectFilter::make('plan')->options(fn (): array => SaasPlan::query()->orderBy('sort')->pluck('code', 'code')->all()),
                SelectFilter::make('type')->options(['business' => __('Business'), 'personal' => __('Personal')]),
                TernaryFilter::make('suspended')
                    ->label(__('Suspended'))
                    ->queries(
                        true: fn (Builder $q): Builder => $q->whereNotNull('settings->suspended_at'),
                        false: fn (Builder $q): Builder => $q->whereNull('settings->suspended_at'),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    TenantActions::changePlan(),
                    TenantActions::suspend(),
                    TenantActions::unsuspend(),
                    TenantActions::impersonateOwner(),
                    RestoreAction::make(),
                ]),
            ]);
    }
}
