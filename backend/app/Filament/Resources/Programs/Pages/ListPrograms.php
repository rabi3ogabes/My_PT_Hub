<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'curated' => Tab::make(__('Global & public'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where(fn (Builder $w) => $w->whereNull('tenant_id')->orWhere('visibility', 'public'))),
            'templates' => Tab::make(__('Tenant templates'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('tenant_id')->where('is_template', true)),
            'all' => Tab::make(__('All')),
        ];
    }
}
