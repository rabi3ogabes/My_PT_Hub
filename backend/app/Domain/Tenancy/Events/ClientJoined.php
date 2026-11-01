<?php

namespace App\Domain\Tenancy\Events;

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;

class ClientJoined
{
    use Dispatchable;

    public function __construct(public readonly Tenant $tenant, public readonly User $client) {}
}
