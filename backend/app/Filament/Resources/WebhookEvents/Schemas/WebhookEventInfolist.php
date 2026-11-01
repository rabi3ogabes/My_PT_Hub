<?php

namespace App\Filament\Resources\WebhookEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebhookEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Event'))
                    ->columns(4)
                    ->schema([
                        TextEntry::make('driver')->badge(),
                        TextEntry::make('type')->placeholder('—'),
                        TextEntry::make('external_id')->copyable(),
                        TextEntry::make('status')->badge()->color(fn (string $state): string => match ($state) {
                            'processed' => 'success', 'failed' => 'danger', 'ignored' => 'gray', default => 'info',
                        }),
                        TextEntry::make('tenant_id')->placeholder('—')->copyable(),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('processed_at')->dateTime()->placeholder('—'),
                        TextEntry::make('error')->color('danger')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make(__('Payload'))
                    ->schema([
                        ViewEntry::make('payload')->hiddenLabel()->view('filament.infolists.json'),
                    ]),
            ]);
    }
}
