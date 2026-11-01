<?php

namespace App\Filament\Resources\Foods\Pages;

use App\Domain\Nutrition\Models\Food;
use App\Filament\Resources\Foods\FoodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFoods extends ListRecords
{
    protected static string $resource = FoodResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        $pending = Food::query()->withoutGlobalScopes()->whereNull('deleted_at')->where('moderation', 'pending')->count();

        return [
            'pending' => Tab::make(__('Moderation queue'))
                ->badge($pending ?: null)->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('moderation', 'pending')),
            'all' => Tab::make(__('All')),
            'approved' => Tab::make(__('Approved'))->modifyQueryUsing(fn (Builder $query): Builder => $query->where('moderation', 'approved')),
            'rejected' => Tab::make(__('Rejected'))->modifyQueryUsing(fn (Builder $query): Builder => $query->where('moderation', 'rejected')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
