<?php

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', fn (User $user, string $id) => $user->id === $id);

Broadcast::channel('tenant.{tenantId}', fn (User $user, string $tenantId) => $user->membershipFor($tenantId) !== null && $user->isStaffIn($tenantId));

// Module channels live in routes/channels/*.php
foreach (glob(__DIR__.'/channels/*.php') ?: [] as $file) {
    require $file;
}
