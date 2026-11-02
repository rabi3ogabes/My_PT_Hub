<?php

use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Services\InvitationService;

beforeEach(fn () => $this->withoutVite());

it('renders the guest auth pages', function (string $uri, string $needle) {
    $this->get($uri)->assertOk()->assertSee($needle, false)->assertSee('lang="en"', false);
})->with([
    'login' => ['/login', 'Email me a magic link'],
    'register' => ['/register', 'Cancel in 2 clicks · No card required'],
    'forgot' => ['/forgot-password', 'Send reset link'],
    'reset' => ['/reset-password/token123?email=a@b.co', 'Save new password'],
]);

it('renders auth pages right-to-left in Arabic', function () {
    $this->get('/login?lang=ar')->assertOk()->assertSee('dir="rtl"', false)->assertSee('lang="ar"', false);
});

it('links Google and Apple sign-in on the login page', function () {
    $this->get('/login')->assertSee(route('social.redirect', 'google'), false)->assertSee(route('social.redirect', 'apple'), false);
});

it('sends guests without a pending challenge away from two-factor', function () {
    $this->get('/two-factor')->assertRedirect(route('login'));
});

it('renders the two-factor challenge when a challenge is pending', function () {
    $this->withSession(['two_factor_challenge' => 'x'])->get('/two-factor')->assertOk()->assertSee(__('web.auth.two_factor_title'));
});

it('renders the invitation page with tenant branding and store options', function () {
    [$tenant, $owner] = $this->createCoachTenant(['name' => 'Sara Haddad'], ['name' => 'Haddad Performance']);
    $invitation = app(InvitationService::class)->create($tenant, $owner, ['email' => 'client@example.com', 'role' => 'client', 'send' => false]);

    $this->get('/invite/'.$invitation->code)->assertOk()
        ->assertSee('Haddad Performance')
        ->assertSee('Sara')
        ->assertSee($invitation->code)
        ->assertSee(__('web.invite.or_web'));
});

it('explains an invalid invitation', function () {
    $this->get('/invite/NOPE123456')->assertOk()->assertSee(__('web.invite.invalid_title'));
});

it('renders the verify email notice for signed-in users', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user)->get('/email/verify')->assertOk()->assertSee(__('web.auth.verify_title'));
});

it('flashes a web translation after email verification', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
    $this->get($url)->assertRedirect('/login')->assertSessionHas('status', __('web.auth.verified'));
});
