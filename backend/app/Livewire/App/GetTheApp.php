<?php

namespace App\Livewire\App;

use App\Http\Middleware\WebTenant;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Clients use the mobile app; this page hands them store badges and a deep link. */
#[Layout('layouts.auth')]
class GetTheApp extends Component
{
    public function render()
    {
        $user = auth()->user();
        $tenant = $user ? WebTenant::resolve($user) : null;
        $coach = null;
        if ($tenant && $user) {
            $coachId = \App\Domain\Tenancy\Models\ClientProfile::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->value('coach_id');
            $coach = $coachId ? \App\Domain\Identity\Models\User::query()->find($coachId, ['id', 'name', 'avatar_path']) : null;
        }

        return view('livewire.app.get-the-app', [
            'tenant' => $tenant,
            'coach' => $coach,
            'isStaff' => $tenant && $user && ($user->isStaffIn($tenant->id)),
        ])->title(__('web.get_app.title'));
    }
}
