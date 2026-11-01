<?php

use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Identity\Models\User;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Support\Impersonator;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'root@aurum.test']);
    $this->admin->forceFill(['is_super_admin' => true])->save();
    [$this->tenant, $this->owner] = $this->createCoachTenant();
});

it('starts impersonation from the users table, audits it and redirects to the app', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListUsers::class)
        ->assertTableActionVisible('impersonate', $this->owner)
        ->callTableAction('impersonate', $this->owner)
        ->assertRedirect('/app');

    expect(auth()->id())->toBe($this->owner->id)
        ->and(session(Impersonator::SESSION_KEY))->toBe($this->admin->id);

    $log = ActivityLog::query()->where('event', 'admin.impersonate.start')->sole();
    expect($log->causer_id)->toBe($this->admin->id)
        ->and($log->subject_id)->toBe($this->owner->id)
        ->and($log->properties['target_email'])->toBe($this->owner->email);
});

it('stops impersonation, restores the admin and audits it', function () {
    $this->actingAs($this->owner)
        ->withSession([Impersonator::SESSION_KEY => $this->admin->id])
        ->get('/impersonate/stop')
        ->assertRedirect('/admin/users');

    $this->assertAuthenticatedAs($this->admin);
    expect(session()->has(Impersonator::SESSION_KEY))->toBeFalse();

    $log = ActivityLog::query()->where('event', 'admin.impersonate.stop')->sole();
    expect($log->causer_id)->toBe($this->admin->id)
        ->and($log->subject_id)->toBe($this->owner->id)
        ->and($log->properties['admin_id'])->toBe($this->admin->id);
});

it('runs a full start → stop round trip over HTTP', function () {
    $this->actingAs($this->admin);
    Livewire::test(ListTenants::class)->callTableAction('impersonateOwner', $this->tenant)->assertRedirect('/app');
    expect(auth()->id())->toBe($this->owner->id);

    $this->get('/impersonate/stop')->assertRedirect('/admin/users');
    $this->assertAuthenticatedAs($this->admin);
    $this->get('/admin')->assertOk();

    expect(ActivityLog::query()->whereIn('event', ['admin.impersonate.start', 'admin.impersonate.stop'])->count())->toBe(2);
});

it('logs out instead of escalating when the impersonator is no longer a super admin', function () {
    $this->admin->forceFill(['is_super_admin' => false])->save();

    $this->actingAs($this->owner)
        ->withSession([Impersonator::SESSION_KEY => $this->admin->id])
        ->get('/impersonate/stop')
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('does nothing on stop without an impersonation session', function () {
    $this->actingAs($this->owner)->get('/impersonate/stop')->assertRedirect('/login');
    expect(ActivityLog::query()->where('event', 'admin.impersonate.stop')->exists())->toBeFalse();
});

it('blocks impersonating another super admin', function () {
    $other = User::factory()->create();
    $other->forceFill(['is_super_admin' => true])->save();
    $this->actingAs($this->admin);

    Livewire::test(ListUsers::class)
        ->assertTableActionHidden('impersonate', $other)
        ->assertTableActionHidden('impersonate', $this->admin)
        ->assertTableActionVisible('impersonate', $this->owner);

    expect(fn () => app(Impersonator::class)->start($this->admin, $other))->toThrow(AuthorizationException::class);
    expect(auth()->id())->toBe($this->admin->id)
        ->and(session()->has(Impersonator::SESSION_KEY))->toBeFalse()
        ->and(ActivityLog::query()->where('event', 'admin.impersonate.start')->exists())->toBeFalse()
        ->and(ActivityLog::query()->where('event', 'admin.impersonate.denied')->where('subject_id', $other->id)->exists())->toBeTrue();
});

it('does not let non admins impersonate', function () {
    expect(app(Impersonator::class)->canImpersonate($this->owner, User::factory()->create()))->toBeFalse();
});
