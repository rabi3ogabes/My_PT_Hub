<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Actions\ImpersonateAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            ImpersonateAction::make(),
            DeleteAction::make()->hidden(fn (User $record): bool => $record->is(auth()->user())),
        ];
    }

    /** @param  array<string, mixed>  $data */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        $wasAdmin = $record->is_super_admin;
        $isAdmin = array_key_exists('is_super_admin', $data) && ! $record->is(auth()->user())
            ? (bool) $data['is_super_admin']
            : $wasAdmin;
        unset($data['is_super_admin']);

        $record->fill($data)->forceFill(['is_super_admin' => $isAdmin])->save();

        ActivityLogger::log('admin.user.updated', $record, ['changes' => array_values(array_diff(array_keys($record->getChanges()), ['password', 'updated_at']))]);
        if ($wasAdmin !== $isAdmin) {
            ActivityLogger::log($isAdmin ? 'admin.user.super_admin_granted' : 'admin.user.super_admin_revoked', $record);
        }

        return $record;
    }
}
