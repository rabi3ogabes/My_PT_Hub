<?php

namespace App\Filament\Resources\SaasSubscriptions\RelationManagers;

use App\Domain\Billing\Models\SaasInvoice;
use App\Domain\Shared\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Invoices';

    public static function shouldSkipAuthorization(): bool
    {
        return auth()->user()?->is_super_admin === true;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->searchable()->copyable(),
                TextColumn::make('amount_minor')->label(__('Amount'))
                    ->formatStateUsing(fn ($state, SaasInvoice $record): string => Money::format((int) $state, $record->currency)),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success', 'refunded' => 'warning', 'open' => 'info', default => 'gray',
                }),
                TextColumn::make('description')->limit(60),
                TextColumn::make('gateway_invoice_id')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_at')->dateTime()->placeholder('—'),
                TextColumn::make('created_at')->dateTime(),
            ]);
    }
}
