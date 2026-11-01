<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Identity\Models\User;
use App\Filament\Support\DateRangeFilter;
use App\Filament\Support\LibraryFilters;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->select('activity_logs.*', 'tenants.name as tenant_name', 'impersonators.email as impersonator_email')
                ->leftJoin('tenants', 'tenants.id', '=', 'activity_logs.tenant_id')
                ->leftJoin('users as impersonators', 'impersonators.id', '=', 'activity_logs.impersonator_id')
                ->with('causer'))
            ->defaultSort(fn (Builder $query): Builder => $query->orderByDesc('activity_logs.created_at'))
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('activity_logs.created_at', $direction))->description(fn (ActivityLog $record): string => $record->created_at?->diffForHumans() ?? ''),
                TextColumn::make('event')->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'admin.impersonate') => 'warning',
                        str_starts_with($state, 'admin.') => 'primary',
                        str_starts_with($state, 'billing.') => 'success',
                        str_contains($state, 'fail') || str_contains($state, 'denied') => 'danger',
                        default => 'gray',
                    })
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('activity_logs.event', 'like', "%{$search}%")),
                TextColumn::make('causer.email')->label(__('Causer'))->placeholder(__('System'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('causer', fn (Builder $q) => $q->where('email', 'like', "%{$search}%"))),
                TextColumn::make('impersonator_email')->label(__('Impersonator'))->placeholder('—')->toggleable(),
                TextColumn::make('tenant_name')->label(__('Tenant'))->placeholder('—'),
                TextColumn::make('subject_type')->label(__('Subject'))
                    ->formatStateUsing(fn (?string $state, ActivityLog $record): string => $state ? $state.' · '.substr((string) $record->subject_id, -6) : '—')
                    ->toggleable(),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options(fn (): array => ActivityLog::query()->distinct()->orderBy('event')->pluck('event', 'event')->all())
                    ->searchable()->multiple(),
                SelectFilter::make('event_prefix')
                    ->label(__('Category'))
                    ->options(['admin.' => 'Admin', 'admin.impersonate' => 'Impersonation', 'billing.' => 'Billing', 'auth.' => 'Auth'])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('activity_logs.event', 'like', $data['value'].'%') : $query),
                LibraryFilters::tenant()->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('activity_logs.tenant_id', $data['value']) : $query),
                SelectFilter::make('causer_id')
                    ->label(__('Causer'))
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => User::query()->where('email', 'like', "%{$search}%")->limit(25)->pluck('email', 'id')->all())
                    ->getOptionLabelUsing(fn ($value): ?string => User::query()->withTrashed()->find($value)?->email)
                    ->options([])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('activity_logs.causer_id', $data['value']) : $query),
                TernaryFilter::make('impersonated')
                    ->label(__('During impersonation'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('activity_logs.impersonator_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('activity_logs.impersonator_id'),
                    ),
                DateRangeFilter::make('activity_logs.created_at'),
            ])
            ->recordActions([
                ViewAction::make()->schema([
                    TextEntry::make('event')->badge(),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('causer.email')->label(__('Causer'))->placeholder(__('System')),
                    TextEntry::make('impersonator_email')->label(__('Impersonator'))->placeholder('—'),
                    TextEntry::make('tenant_name')->label(__('Tenant'))->placeholder('—'),
                    TextEntry::make('subject_type')->placeholder('—'),
                    TextEntry::make('subject_id')->placeholder('—')->copyable(),
                    TextEntry::make('ip')->label('IP')->placeholder('—'),
                    TextEntry::make('user_agent')->placeholder('—'),
                    ViewEntry::make('properties')->view('filament.infolists.json'),
                ]),
            ]);
    }
}
