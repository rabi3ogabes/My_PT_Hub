<?php

namespace App\Filament\Pages;

use App\Filament\Support\Nav;
use App\Filament\Support\SystemHealth as Checks;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SystemHealth extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.system-health';

    public static function getNavigationGroup(): ?string
    {
        return Nav::operations();
    }

    public static function getNavigationLabel(): string
    {
        return __('System health');
    }

    public function getTitle(): string
    {
        return __('System health');
    }

    /** @return list<array{key: string, label: string, status: string, value: string, detail: ?string}> */
    public function getChecks(): array
    {
        return app(Checks::class)->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('Run checks again'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(fn () => null),
        ];
    }
}
