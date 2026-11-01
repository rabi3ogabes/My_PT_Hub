<?php

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Message;
use App\Domain\Nutrition\Models\Meal;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\MealPlanDay;
use App\Domain\Payments\Gateways\Drivers\TapGateway;
use App\Domain\Payments\Jobs\FulfilOrder;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Services\CheckoutService;
use App\Domain\Tenancy\Models\ClientGroup;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Notifications\GenericNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Payments\Support\BuildsPayments;

uses(BuildsPayments::class);

beforeEach(function () {
    Notification::fake();
    Storage::fake('local');
    [$this->tenant, $this->owner] = $this->coachIn('QA', 'QAR');
    $this->content = $this->inTenant($this->tenant, function () {
        $program = Program::create(['name' => ['en' => '12-week strength'], 'level' => 'beginner']);
        $plan = MealPlan::create(['name' => 'Lean bulk template', 'is_template' => true, 'status' => 'active']);
        $day = MealPlanDay::create(['meal_plan_id' => $plan->id, 'day_number' => 1, 'label' => 'Training day']);
        Meal::create(['meal_plan_day_id' => $day->id, 'slot' => 'breakfast', 'name' => 'Oats', 'position' => 0]);
        $habit = Habit::create(['name' => ['en' => 'Drink 3L water'], 'type' => 'count', 'target' => 3]);

        return [
            'program' => $program,
            'plan' => $plan,
            'habit' => $habit,
            'form' => CheckinForm::query()->firstOrFail(),
            'group' => ClientGroup::create(['name' => '12-week cohort']),
        ];
    });
    [$this->product, $this->price] = $this->makeProduct($this->tenant, ['fulfilment' => [
        'program_id' => $this->content['program']->id,
        'meal_plan_id' => $this->content['plan']->id,
        'habit_ids' => [$this->content['habit']->id],
        'checkin_form_id' => $this->content['form']->id,
        'group_id' => $this->content['group']->id,
        'credits' => 8,
        'welcome_message' => ['en' => 'Welcome {first_name}! Let us start.'],
    ]]);
    $this->gateway = $this->connectGateway($this->tenant, 'tap');
    Http::fake(['api.tap.company/v2/charges' => Http::response(['id' => 'chg_F1', 'transaction' => ['url' => 'https://tap.test/pay']])]);
});

function tapPaid(string $orderId, string $chargeId = 'chg_F1'): array
{
    $data = ['id' => $chargeId, 'amount' => 399, 'currency' => 'QAR', 'status' => 'CAPTURED', 'reference' => ['gateway' => 'g', 'payment' => 'p', 'order' => $orderId], 'transaction' => ['created' => '1']];

    return [json_encode($data), ['hashstring' => TapGateway::hashString($data, 'sk_test_tap')]];
}

it('delivers everything a package includes when the payment webhook arrives', function () {
    $orderId = $this->postJson("/api/v1/public/checkout/{$this->tenant->slug}/{$this->product->slug}", [
        'price_id' => $this->price->id, 'email' => 'Sara.New@Example.com', 'name' => 'Sara New', 'locale' => 'ar',
    ])->assertCreated()->json('data.order_id');

    [$body, $headers] = tapPaid($orderId);
    $this->postWebhook($this->gateway, $body, $headers)->assertOk();

    $client = User::query()->where('email', 'sara.new@example.com')->firstOrFail();
    $order = Order::withoutGlobalScopes()->findOrFail($orderId);
    expect($order->client_id)->toBe($client->id)
        ->and($order->fulfilled_at)->not->toBeNull()
        ->and($client->isClientIn($this->tenant->id))->toBeTrue()
        ->and($client->locale)->toBe('ar');

    $this->inTenant($this->tenant, function () use ($client, $order) {
        expect(ProgramAssignment::query()->where('client_id', $client->id)->where('program_id', $this->content['program']->id)->count())->toBe(1);
        $copy = MealPlan::query()->where('client_id', $client->id)->firstOrFail();
        expect($copy->is_template)->toBeFalse()->and($copy->id)->not->toBe($this->content['plan']->id)->and($copy->days()->first()->meals()->count())->toBe(1);
        expect(HabitAssignment::query()->where('client_id', $client->id)->where('habit_id', $this->content['habit']->id)->where('is_active', true)->count())->toBe(1);
        expect(CheckinSchedule::query()->where('client_id', $client->id)->where('checkin_form_id', $this->content['form']->id)->count())->toBe(1);
        expect($this->content['group']->members()->where('users.id', $client->id)->exists())->toBeTrue();
        expect(CreditLedgerEntry::balanceFor($client->id))->toBe(8);
        expect(Invitation::query()->where('email', 'sara.new@example.com')->exists())->toBeTrue();
        $message = Message::query()->where('sender_id', $this->owner->id)->latest()->firstOrFail();
        expect($message->body)->toBe('Welcome Sara! Let us start.');
        expect($order->meta['fulfilment'])->toHaveKeys(['program_assignment_id', 'meal_plan_id', 'habit_assignment_ids', 'checkin_schedule_id', 'group_id', 'credits', 'conversation_id']);
    });

    Notification::assertSentTo($this->owner, GenericNotification::class);
});

it('processes the same webhook twice but fulfils once', function () {
    $orderId = $this->postJson("/api/v1/public/checkout/{$this->tenant->slug}/{$this->product->slug}", [
        'price_id' => $this->price->id, 'email' => 'twice@example.com', 'name' => 'Twice',
    ])->json('data.order_id');
    [$body, $headers] = tapPaid($orderId);

    $this->postWebhook($this->gateway, $body, $headers)->assertJsonPath('status', 'processed');
    $this->postWebhook($this->gateway, $body, $headers)->assertJsonPath('status', 'duplicate');

    // A different event for the same payment (e.g. provider re-sent with a new id) is also a no-op.
    $other = json_decode($body, true);
    $other['transaction']['created'] = '2';
    $this->postWebhook($this->gateway, json_encode($other), ['hashstring' => TapGateway::hashString($other, 'sk_test_tap')])->assertOk();
    FulfilOrder::dispatchSync($orderId);

    $client = User::query()->where('email', 'twice@example.com')->firstOrFail();
    $this->inTenant($this->tenant, function () use ($client, $orderId) {
        expect(Payment::query()->where('order_id', $orderId)->where('status', 'succeeded')->count())->toBe(1)
            ->and(CreditLedgerEntry::query()->where('client_id', $client->id)->count())->toBe(1)
            ->and(CreditLedgerEntry::balanceFor($client->id))->toBe(8)
            ->and(ProgramAssignment::query()->where('client_id', $client->id)->count())->toBe(1)
            ->and(MealPlan::query()->where('client_id', $client->id)->count())->toBe(1)
            ->and(Message::query()->where('sender_id', $this->owner->id)->count())->toBe(1)
            ->and(Invoice::query()->where('order_id', $orderId)->count())->toBe(1);
    });
});

it('runs the same fulfilment for a cash payment recorded by the coach, with receipt photo', function () {
    $client = $this->addClient($this->tenant, $this->owner);

    $response = $this->actingAsIn($this->owner, $this->tenant)->post('/api/v1/manual-payments', [
        'product_id' => $this->product->id,
        'price_id' => $this->price->id,
        'client_id' => $client->id,
        'method' => 'cash',
        'receipt' => UploadedFile::fake()->image('receipt.jpg'),
    ], ['Accept' => 'application/json']);

    $response->assertCreated()->assertJsonPath('data.status', 'paid')->assertJsonPath('data.payments.0.method', 'cash')->assertJsonPath('data.payments.0.has_receipt', true);
    $order = Order::withoutGlobalScopes()->findOrFail($response->json('data.id'));
    expect($order->fulfilled_at)->not->toBeNull();
    $payment = $order->payments()->firstOrFail();
    expect($payment->driver)->toBe('manual')->and($payment->recorded_by)->toBe($this->owner->id);
    Storage::disk('local')->assertExists($payment->receipt_path);

    $this->inTenant($this->tenant, function () use ($client) {
        expect(ProgramAssignment::query()->where('client_id', $client->id)->count())->toBe(1)
            ->and(CreditLedgerEntry::balanceFor($client->id))->toBe(8)
            ->and(HabitAssignment::query()->where('client_id', $client->id)->count())->toBe(1);
    });
});

it('settles a pending manual order (bank transfer) and fulfils it', function () {
    $manual = $this->connectGateway($this->tenant, 'manual', ['is_default' => false]);
    $order = $this->inTenant($this->tenant, fn () => app(CheckoutService::class)
        ->createOrder($this->tenant, $this->product, $this->price, ['email' => 'transfer@example.com', 'name' => 'Bank Client'], gateway: $manual));

    $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/manual-payments', ['order_id' => $order->id, 'method' => 'bank_transfer', 'reference' => 'TRX-889'])
        ->assertCreated()->assertJsonPath('data.status', 'paid');
    expect($order->refresh()->fulfilled_at)->not->toBeNull();

    $this->actingAsIn($this->owner, $this->tenant)->postJson('/api/v1/manual-payments', ['order_id' => $order->id, 'method' => 'cash'])
        ->assertStatus(409);
});

it('forbids assistants from recording payments', function () {
    $assistant = $this->addStaff($this->tenant, 'assistant');
    $client = $this->addClient($this->tenant, $this->owner);
    expectApiError($this->actingAsIn($assistant, $this->tenant)->postJson('/api/v1/manual-payments', [
        'product_id' => $this->product->id, 'price_id' => $this->price->id, 'client_id' => $client->id, 'method' => 'cash',
    ]), 403, 'forbidden');
});
