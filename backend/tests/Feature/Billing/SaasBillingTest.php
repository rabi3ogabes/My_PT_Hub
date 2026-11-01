<?php

use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Billing\Services\SaasBillingService;
use App\Notifications\Billing\RenewalReminder;
use App\Notifications\Billing\SubscriptionCancelled;
use Illuminate\Support\Facades\Notification;

it('starts every new coach on a card-free Pro trial', function () {
    [$tenant] = $this->createCoachTenant();
    expect($tenant->plan)->toBe('pro')
        ->and($tenant->subscription->status)->toBe('trialing')
        ->and($tenant->subscription->trial_ends_at->isFuture())->toBeTrue();
});

it('cancels a monthly plan at period end in one call after confirmation', function () {
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAsIn($owner, $tenant)->postJson('/api/v1/billing/subscribe', ['plan' => 'pro', 'interval' => 'month'])->assertOk();

    $res = $this->postJson('/api/v1/billing/cancel', ['reason' => 'too_expensive'])->assertOk();
    $sub = $tenant->refresh()->subscription;
    expect($sub->cancel_at_period_end)->toBeTrue()->and($tenant->plan)->toBe('pro')
        ->and($res->json('data.refund_minor'))->toBe(0);
    Notification::assertSentTo($owner, SubscriptionCancelled::class);

    $this->travelTo($sub->current_period_end->addMinute());
    app(SaasBillingService::class)->expireEnded();
    expect($tenant->refresh()->plan)->toBe('free');
});

it('refunds the unused part of an annual plan pro-rata', function () {
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAsIn($owner, $tenant)->postJson('/api/v1/billing/subscribe', ['plan' => 'pro', 'interval' => 'year'])->assertOk();
    $this->travel(73)->days(); // ~20% of the year used

    $refund = $this->postJson('/api/v1/billing/cancel')->assertOk()->json('data.refund_minor');
    expect($refund)->toBeGreaterThan(30000)->toBeLessThan(32000) // ≈ 80% of 39000
        ->and($tenant->refresh()->plan)->toBe('free');
});

it('lets the coach resume before the period ends', function () {
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();
    $this->actingAsIn($owner, $tenant)->postJson('/api/v1/billing/subscribe', ['plan' => 'studio', 'interval' => 'month']);
    $this->postJson('/api/v1/billing/cancel');
    $this->postJson('/api/v1/billing/resume')->assertOk()->assertJsonPath('data.subscription.cancel_at_period_end', false);
});

it('sends 7-day and 1-day reminders exactly once', function () {
    Notification::fake();
    [$tenant, $owner] = $this->createCoachTenant();
    $sub = $tenant->subscription;
    $sub->update(['current_period_end' => now()->addDays(7), 'trial_ends_at' => now()->addDays(7)]);

    $svc = app(SaasBillingService::class);
    expect($svc->sendReminders())->toBe(1)->and($svc->sendReminders())->toBe(0);
    $this->travel(6)->days();
    expect($svc->sendReminders())->toBe(1);
    Notification::assertSentToTimes($owner, RenewalReminder::class, 2);
});

it('processes Stripe webhooks idempotently', function () {
    [$tenant] = $this->createCoachTenant();
    $event = ['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => [
        'client_reference_id' => $tenant->id, 'customer' => 'cus_1', 'subscription' => 'sub_1',
        'metadata' => ['tenant_id' => $tenant->id, 'plan' => 'studio', 'interval' => 'month'],
    ]]];
    $svc = app(SaasBillingService::class);
    $svc->handleWebhook($event);
    $svc->handleWebhook($event);

    expect(SaasSubscription::where('gateway_subscription_id', 'sub_1')->count())->toBe(1)
        ->and($tenant->refresh()->plan)->toBe('studio');
});

it('verifies Stripe webhook signatures', function () {
    $gw = new \App\Domain\Billing\Gateways\StripeSaasGateway('sk_test', 'whsec_test');
    $payload = json_encode(['id' => 'evt_2', 'type' => 'invoice.paid']);
    $t = time();
    $sig = 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$payload, 'whsec_test');
    expect($gw->verifyWebhook($payload, $sig)['id'])->toBe('evt_2');
    expect(fn () => $gw->verifyWebhook($payload, 't='.$t.',v1=deadbeef'))->toThrow(\App\Domain\Shared\Support\ApiException::class);
});

it('restricts billing to owners', function () {
    [$tenant] = $this->createCoachTenant();
    $coach = $this->addStaff($tenant, 'coach');
    expectApiError($this->actingAsIn($coach, $tenant)->postJson('/api/v1/billing/cancel'), 403, 'forbidden');
});
