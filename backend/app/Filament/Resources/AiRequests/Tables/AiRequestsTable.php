<?php

namespace App\Filament\Resources\AiRequests\Tables;

use App\Domain\Ai\Models\AiRequest;
use App\Filament\Support\DateRangeFilter;
use App\Filament\Support\LibraryFilters;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AiRequestsTable
{
    public static function usd(int|float|string|null $micros): string
    {
        return '$'.number_format(((float) $micros) / 1_000_000, 4);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->select('ai_requests.*', 'tenants.name as tenant_name')
                ->leftJoin('tenants', 'tenants.id', '=', 'ai_requests.tenant_id'))
            ->defaultSort(fn (Builder $query): Builder => $query->orderByDesc('ai_requests.created_at'))
            ->groups([
                Group::make('ai_requests.tenant_id')->label(__('Tenant'))
                    ->getTitleFromRecordUsing(fn (AiRequest $record): string => (string) ($record->getAttribute('tenant_name') ?? __('Platform / standalone')))
                    ->collapsible(),
                Group::make('ai_requests.feature')->label(__('Feature'))->collapsible(),
                Group::make('ai_requests.model')->label(__('Model'))->collapsible(),
            ])
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('ai_requests.created_at', $direction)),
                TextColumn::make('tenant_name')->label(__('Tenant'))->placeholder('—'),
                TextColumn::make('feature')->badge()->color('primary'),
                TextColumn::make('provider')->badge()->color('gray'),
                TextColumn::make('model')->toggleable(),
                TextColumn::make('tokens_in')->label(__('Tokens in'))->numeric()
                    ->summarize(Sum::make()->label(__('Total'))->numeric()),
                TextColumn::make('tokens_out')->label(__('Tokens out'))->numeric()
                    ->summarize(Sum::make()->label(__('Total'))->numeric()),
                TextColumn::make('cost_micros')->label(__('Cost'))
                    ->formatStateUsing(fn ($state): string => self::usd($state))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('ai_requests.cost_micros', $direction))
                    ->summarize(Sum::make()->label(__('Total'))->formatStateUsing(fn ($state): string => self::usd($state))),
                TextColumn::make('latency_ms')->label(__('Latency'))->suffix(' ms')->numeric()
                    ->summarize(Average::make()->label(__('Avg'))->numeric(decimalPlaces: 0)),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'ok' => 'success', 'error' => 'danger', default => 'warning',
                }),
                TextColumn::make('error')->limit(40)->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                LibraryFilters::tenant()->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('ai_requests.tenant_id', $data['value']) : $query),
                SelectFilter::make('feature')
                    ->options(fn (): array => AiRequest::query()->distinct()->orderBy('feature')->pluck('feature', 'feature')->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('ai_requests.feature', $data['value']) : $query),
                SelectFilter::make('provider')
                    ->options(fn (): array => AiRequest::query()->distinct()->orderBy('provider')->pluck('provider', 'provider')->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('ai_requests.provider', $data['value']) : $query),
                SelectFilter::make('status')
                    ->options(['ok' => 'OK', 'invalid' => 'Invalid', 'refused' => 'Refused', 'error' => 'Error'])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null) ? $query->where('ai_requests.status', $data['value']) : $query),
                DateRangeFilter::make('ai_requests.created_at')
                    ->default(['from' => now()->startOfMonth()->toDateString(), 'until' => null]),
            ]);
    }
}
