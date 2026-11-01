<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @param  array<string, mixed>  $data */
    protected function handleRecordCreation(array $data): Model
    {
        $isAdmin = (bool) ($data['is_super_admin'] ?? false);
        unset($data['is_super_admin']);

        $user = new User($data);
        $user->forceFill(['is_super_admin' => $isAdmin])->save();
        ActivityLogger::log('admin.user.created', $user, ['super_admin' => $isAdmin]);

        return $user;
    }
}
