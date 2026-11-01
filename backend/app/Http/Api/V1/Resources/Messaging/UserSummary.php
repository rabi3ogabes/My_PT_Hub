<?php

namespace App\Http\Api\V1\Resources\Messaging;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Storage;

/** Compact user shape {id, name, avatar_url} shared by messaging, community and calendar resources. */
final class UserSummary
{
    /** @return array{id: string, name: string, avatar_url: string|null}|null */
    public static function make(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
        ];
    }
}
