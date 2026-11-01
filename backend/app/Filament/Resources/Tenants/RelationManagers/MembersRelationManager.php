<?php

namespace App\Filament\Resources\Tenants\RelationManagers;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\MembershipService;
use App\Filament\Actions\ImpersonateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'memberships';

    protected static ?string $title = 'Members';

    public static function shouldSkipAuthorization(): bool
    {
        return auth()->user()?->is_super_admin === true;
    }

    /** @return array<string, string> */
    private static function roles(): array
    {
        return collect(User::ROLES)->mapWithKeys(fn (string $r): array => [$r => ucfirst($r)])->all();
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->recordTitleAttribute('role')
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('user.name')->label(__('Name'))->searchable(),
                TextColumn::make('user.email')->label(__('Email'))->searchable()->copyable(),
                TextColumn::make('role')->badge()->color(fn (string $state): string => match ($state) {
                    'owner' => 'primary', 'coach' => 'info', 'assistant' => 'gray', default => 'success',
                }),
                TextColumn::make('status')->badge(),
                TextColumn::make('joined_at')->dateTime()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(self::roles()),
                SelectFilter::make('status')->options(['active' => 'Active', 'invited' => 'Invited', 'archived' => 'Archived']),
            ])
            ->headerActions([
                Action::make('addMember')
                    ->label(__('Add member'))
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->schema([
                        Select::make('user_id')
                            ->label(__('User'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => User::query()
                                ->where(fn ($q) => $q->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))
                                ->limit(25)->get()->mapWithKeys(fn (User $u): array => [$u->id => "{$u->name} <{$u->email}>"])->all())
                            ->getOptionLabelUsing(fn ($value): ?string => User::query()->find($value)?->email)
                            ->required(),
                        Select::make('role')->options(self::roles())->required()->default('client'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Tenant $tenant */
                        $tenant = $this->getOwnerRecord();
                        $user = User::query()->findOrFail($data['user_id']);
                        app(TenantContext::class)->run($tenant, fn () => app(MembershipService::class)->add($tenant, $user, $data['role'], enforceLimits: false));
                        ActivityLogger::log('admin.member.added', $user, ['role' => $data['role']], tenantId: $tenant->id);
                        Notification::make()->title(__('Member added'))->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('changeRole')
                    ->label(__('Change role'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->fillForm(fn (Membership $record): array => ['role' => $record->role])
                    ->schema([Select::make('role')->options(self::roles())->required()])
                    ->action(function (Membership $record, array $data): void {
                        /** @var Tenant $tenant */
                        $tenant = $this->getOwnerRecord();
                        app(TenantContext::class)->run($tenant, fn () => app(MembershipService::class)->changeRole($tenant, $record->user, $data['role']));
                        ActivityLogger::log('admin.member.role_changed', $record->user, ['from' => $record->role, 'to' => $data['role']], tenantId: $tenant->id);
                        Notification::make()->title(__('Role updated'))->success()->send();
                    }),
                Action::make('archive')
                    ->label(__('Archive'))
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Membership $record): bool => $record->status !== 'archived')
                    ->action(function (Membership $record): void {
                        /** @var Tenant $tenant */
                        $tenant = $this->getOwnerRecord();
                        app(TenantContext::class)->run($tenant, fn () => app(MembershipService::class)->archive($tenant, $record->user));
                        ActivityLogger::log('admin.member.archived', $record->user, tenantId: $tenant->id);
                        Notification::make()->title(__('Member archived'))->success()->send();
                    }),
                ImpersonateAction::make(fn (Model $record): ?User => $record instanceof Membership ? $record->user : null),
            ]);
    }
}
