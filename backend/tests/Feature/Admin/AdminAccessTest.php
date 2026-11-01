<?php

use App\Domain\Identity\Models\User;

function makeSuperAdmin(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['is_super_admin' => true])->save();

    return $admin->refresh();
}

it('redirects guests to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk()->assertSee(config('brand.name').' Admin');
});

it('forbids non super admins', function () {
    [, $owner] = $this->createCoachTenant();

    $this->actingAs($owner)->get('/admin')->assertForbidden();
    $this->actingAs($owner)->get('/admin/tenants')->assertForbidden();
});

it('lets super admins reach every admin page', function (string $url) {
    [$tenant] = $this->createCoachTenant();

    $this->actingAs(makeSuperAdmin())->get($url)->assertOk();
})->with([
    'dashboard' => '/admin',
    'tenants' => '/admin/tenants',
    'tenant create' => '/admin/tenants/create',
    'users' => '/admin/users',
    'users create' => '/admin/users/create',
    'plans' => '/admin/saas-plans',
    'plans create' => '/admin/saas-plans/create',
    'subscriptions' => '/admin/saas-subscriptions',
    'feature flags' => '/admin/feature-flags',
    'translations' => '/admin/translations',
    'exercises' => '/admin/exercises',
    'exercise create' => '/admin/exercises/create',
    'foods' => '/admin/foods',
    'food create' => '/admin/foods/create',
    'recipes' => '/admin/recipes',
    'recipe create' => '/admin/recipes/create',
    'programs' => '/admin/programs',
    'program create' => '/admin/programs/create',
    'blog posts' => '/admin/blog-posts',
    'blog create' => '/admin/blog-posts/create',
    'coach profiles' => '/admin/coach-profiles',
    'reviews' => '/admin/reviews',
    'webhook events' => '/admin/webhook-events',
    'activity log' => '/admin/activity-logs',
    'ai usage' => '/admin/ai-requests',
    'marketing leads' => '/admin/marketing-leads',
    'system health' => '/admin/system-health',
    'white-label apps' => '/admin/white-label-apps',
]);
