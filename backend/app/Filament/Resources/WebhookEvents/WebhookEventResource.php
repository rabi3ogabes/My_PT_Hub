<?php

namespace App\Filament\Resources\WebhookEvents;

use App\Domain\Payments\Models\WebhookEvent;
use App\Filament\Resources\WebhookEvents\Pages\ListWebhookEvents;
use App\Filament\Resources\WebhookEvents\Pages\ViewWebhookEvent;
use App\Filament\Resources\WebhookEvents\Schemas\WebhookEventInfolist;
use App\Filament\Resources\WebhookEvents\Tables\WebhookEventsTable;
use App\Filament\Support\AdminResource;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WebhookEventResource extends AdminResource
{
    protected static ?string $model = WebhookEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $recordTitleAttribute = 'external_id';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return Nav::billing();
    }

    public static function getNavigationBadge(): ?string
    {
        $failed = WebhookEvent::query()->where('status', 'failed')->count();

        return $failed > 0 ? (string) $failed : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return WebhookEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebhookEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhookEvents::route('/'),
            'view' => ViewWebhookEvent::route('/{record}'),
        ];
    }
}
