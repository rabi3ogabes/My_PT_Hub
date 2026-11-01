<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Domain\Tenancy\Models\Membership;
use App\Filament\Resources\Tenants\TenantResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipsRelationManager extends RelationManager
{
    protected static string $relationship = 'memberships';

    protected static ?string $title = 'Tenants';

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
            ->modifyQueryUsing(fn ($query) => $query->with('tenant'))
            ->columns([
                TextColumn::make('tenant.name')->label(__('Tenant')),
                TextColumn::make('tenant.plan')->label(__('Plan'))->badge()->color('primary'),
                TextColumn::make('role')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('joined_at')->dateTime()->placeholder('—'),
            ])
            ->recordActions([
                Action::make('openTenant')
                    ->label(__('Open tenant'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Membership $record): string => TenantResource::getUrl('view', ['record' => $record->tenant_id])),
            ]);
    }
}
