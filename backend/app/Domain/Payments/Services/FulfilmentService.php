<?php

namespace App\Domain\Payments\Services;

use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Messaging\Models\Message;
use App\Domain\Nutrition\Models\Meal;
use App\Domain\Nutrition\Models\MealItem;
use App\Domain\Nutrition\Models\MealPlan;
use App\Domain\Nutrition\Models\MealPlanDay;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Support\Notify;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Support\Money;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientGroup;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\InvitationService;
use App\Domain\Tenancy\Services\MembershipService;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Delivers what was bought: client account + membership, program, meal plan,
 * habits, check-in schedule, session credits, group, welcome message, coach
 * notification. Idempotent through orders.fulfilled_at; revocable after expiry.
 */
final class FulfilmentService
{
    private const PROGRAM_ASSIGNER = 'App\\Domain\\Workouts\\Services\\ProgramAssigner';

    private const MESSAGING = 'App\\Domain\\Messaging\\Services\\MessagingService';

    public function __construct(
        private readonly TenantContext $context,
        private readonly MembershipService $memberships,
    ) {}

    /** Ensures the buyer has an account and an active client membership in the tenant. */
    public function ensureClient(Order $order): User
    {
        $tenant = Tenant::findOrFail($order->tenant_id);

        return $this->context->run($tenant, function () use ($order, $tenant) {
            $client = $order->client_id ? User::find($order->client_id) : null;
            $isNew = false;
            if (! $client) {
                $email = strtolower(trim((string) $order->customer_email));
                if ($email === '') {
                    throw ApiException::invalid('customer_required', __('payments.errors.customer_required'));
                }
                $client = User::query()->where('email', $email)->first();
                if (! $client) {
                    $client = User::create([
                        'name' => $order->customer_name ?: Str::before($email, '@'),
                        'email' => $email,
                        'locale' => $order->meta['locale'] ?? $tenant->locale ?? 'en',
                        'timezone' => $tenant->timezone ?? 'UTC',
                    ]);
                    $isNew = true;
                }
                $order->forceFill(['client_id' => $client->id])->save();
            }

            $coachId = $this->coachIdFor($order, $tenant, $client);
            if ($client->roleIn($tenant->id) === null) {
                $this->memberships->add($tenant, $client, 'client', $coachId, enforceLimits: false);
                $this->invite($tenant, $client, $coachId, $order, $isNew);
            }

            return $client;
        });
    }

    private function coachIdFor(Order $order, Tenant $tenant, User $client): ?string
    {
        $order->loadMissing('items.product');
        $product = $order->items->first()?->product;
        $fromProduct = $product?->fulfilment['coach_id'] ?? null;
        if ($fromProduct) {
            return $fromProduct;
        }
        $profileCoach = ClientProfile::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $client->id)->value('coach_id');

        return $profileCoach ?? $tenant->users()->wherePivot('role', 'owner')->value('users.id');
    }

    private function invite(Tenant $tenant, User $client, ?string $coachId, Order $order, bool $isNew): void
    {
        $inviter = $coachId ? User::find($coachId) : null;
        if (! $inviter) {
            return;
        }
        $data = ['email' => $client->email, 'name' => $client->name, 'role' => 'client', 'payload' => ['order_id' => $order->id], 'send' => true];
        try {
            app(InvitationService::class)->create($tenant, $inviter, $data);
        } catch (ApiException) {
            // The purchase already granted the seat: plan limits do not block a paying client's invitation.
            $invitation = Invitation::create([
                'tenant_id' => $tenant->id,
                'invited_by' => $inviter->id,
                'code' => strtoupper(Str::random(10)),
                'email' => strtolower($client->email),
                'name' => $client->name,
                'role' => 'client',
                'payload' => $data['payload'],
                'expires_at' => now()->addDays(14),
            ]);
            Notification::route('mail', $client->email)->notify(new InvitationNotification($invitation, $tenant, $inviter));
        }
    }

    /** Assigns everything the product delivers. Safe to run any number of times. */
    public function fulfil(Order $order): Order
    {
        $tenant = Tenant::findOrFail($order->tenant_id);
        $notify = null;

        $result = $this->context->run($tenant, function () use ($order, $tenant, &$notify) {
            return DB::transaction(function () use ($order, $tenant, &$notify) {
                /** @var Order $locked */
                $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($locked->fulfilled_at || ! $locked->isPaid()) {
                    return $locked;
                }
                $client = $this->ensureClient($locked);
                $locked->refresh()->load('items');
                $product = Product::withTrashed()->find($locked->items->first()?->product_id);
                $f = $product?->fulfilment ?? [];
                $renewal = (bool) ($locked->meta['renewal'] ?? false);
                $coachId = $this->coachIdFor($locked, $tenant, $client);
                $granted = [];

                if (! $renewal) {
                    $granted += $this->assignContent($tenant, $client, $f, $coachId);
                    $welcome = $this->welcome($tenant, $client, $product, $f, $coachId);
                    if ($welcome) {
                        $granted['conversation_id'] = $welcome;
                    }
                }

                $credits = (int) ($f['credits'] ?? 0) * max(1, (int) $locked->items->sum('quantity'));
                if ($credits > 0 && (! $renewal || $product?->type === 'membership')) {
                    $already = CreditLedgerEntry::query()->where('order_id', $locked->id)->where('reason', 'purchase')->exists();
                    if (! $already) {
                        CreditLedgerEntry::create([
                            'client_id' => $client->id,
                            'amount' => $credits,
                            'reason' => 'purchase',
                            'order_id' => $locked->id,
                            'note' => mb_substr((string) $product?->translate('name', $tenant->locale), 0, 250),
                            'expires_on' => isset($f['credits_expire_days']) ? now()->addDays((int) $f['credits_expire_days'])->toDateString() : null,
                        ]);
                    }
                    $granted['credits'] = $credits;
                }

                $accessDays = (int) ($f['access_days'] ?? 0);
                $locked->forceFill([
                    'fulfilled_at' => now(),
                    'access_expires_at' => $accessDays > 0 && ! $locked->client_subscription_id ? ($locked->paid_at ?? now())->copy()->addDays($accessDays) : $locked->access_expires_at,
                    'meta' => array_merge($locked->meta ?? [], ['fulfilment' => $granted]),
                ])->save();
                ActivityLogger::log('payments.order_fulfilled', $locked, ['granted' => array_keys($granted)], tenantId: $tenant->id);

                if (! $renewal) {
                    $notify = [
                        'client' => $client->name,
                        'product' => (string) $product?->translate('name', $tenant->locale),
                        'amount' => Money::format($locked->total_minor, $locked->currency, $tenant->locale),
                        'coach_id' => $coachId,
                    ];
                }

                return $locked;
            });
        });

        if ($notify) {
            Notify::staff($tenant, 'new_sale', $notify, '/orders/'.$result->id, $notify['coach_id']);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $f
     * @return array<string, mixed>
     */
    private function assignContent(Tenant $tenant, User $client, array $f, ?string $coachId): array
    {
        $granted = [];
        $today = now($tenant->timezone ?: 'UTC')->toDateString();

        if (! empty($f['program_id']) && class_exists(self::PROGRAM_ASSIGNER)) {
            $assignment = app(self::PROGRAM_ASSIGNER)->assignById((string) $f['program_id'], $client, $today, $coachId);
            $granted['program_assignment_id'] = is_object($assignment) ? ($assignment->id ?? null) : null;
        }

        if (! empty($f['meal_plan_id'])) {
            $plan = $this->copyMealPlan((string) $f['meal_plan_id'], $client, $coachId, $today);
            if ($plan) {
                $granted['meal_plan_id'] = $plan->id;
            }
        }

        if (! empty($f['habit_ids'])) {
            $ids = [];
            foreach (Habit::query()->whereIn('id', (array) $f['habit_ids'])->get() as $habit) {
                $assignment = HabitAssignment::query()->firstOrCreate(
                    ['habit_id' => $habit->id, 'client_id' => $client->id, 'is_active' => true],
                    ['schedule' => 'daily', 'target' => $habit->target, 'start_date' => $today],
                );
                $ids[] = $assignment->id;
            }
            $granted['habit_assignment_ids'] = $ids;
        }

        if (! empty($f['checkin_form_id']) && CheckinForm::query()->whereKey($f['checkin_form_id'])->exists()) {
            $weekday = (int) ($f['checkin_weekday'] ?? now($tenant->timezone ?: 'UTC')->isoWeekday());
            $schedule = CheckinSchedule::query()->updateOrCreate(
                ['checkin_form_id' => $f['checkin_form_id'], 'client_id' => $client->id],
                ['frequency' => 'weekly', 'weekday' => $weekday, 'next_due_on' => now($tenant->timezone ?: 'UTC')->next($weekday % 7)->toDateString(), 'is_active' => true],
            );
            $granted['checkin_schedule_id'] = $schedule->id;
        }

        if (! empty($f['group_id']) && ($group = ClientGroup::query()->find($f['group_id']))) {
            $group->members()->syncWithoutDetaching([$client->id]);
            $granted['group_id'] = $group->id;
        }

        return $granted;
    }

    private function copyMealPlan(string $templateId, User $client, ?string $coachId, string $today): ?MealPlan
    {
        $template = MealPlan::query()->with('days.meals.items')->find($templateId);
        if (! $template) {
            return null;
        }
        $plan = MealPlan::create([
            'created_by' => $coachId,
            'client_id' => $client->id,
            'name' => $template->name,
            'type' => $template->type,
            'diet_tags' => $template->diet_tags,
            'is_template' => false,
            'fasting' => $template->fasting,
            'start_date' => $today,
            'status' => 'active',
        ]);
        foreach ($template->days as $day) {
            $newDay = MealPlanDay::create(['meal_plan_id' => $plan->id, 'day_number' => $day->day_number, 'label' => $day->label, 'day_type' => $day->day_type]);
            foreach ($day->meals as $meal) {
                $newMeal = Meal::create(['meal_plan_day_id' => $newDay->id, 'slot' => $meal->slot, 'name' => $meal->name, 'time' => $meal->time, 'position' => $meal->position]);
                foreach ($meal->items as $item) {
                    MealItem::create(['meal_id' => $newMeal->id, 'food_id' => $item->food_id, 'recipe_id' => $item->recipe_id, 'quantity' => $item->quantity, 'unit' => $item->unit, 'position' => $item->position]);
                }
            }
        }

        return $plan;
    }

    /** @param array<string, mixed> $f */
    private function welcome(Tenant $tenant, User $client, ?Product $product, array $f, ?string $coachId): ?string
    {
        $coach = $coachId ? User::find($coachId) : null;
        if (! $coach || $coach->id === $client->id) {
            return null;
        }
        $text = $f['welcome_message'] ?? null;
        if (is_array($text)) {
            $text = $text[$client->preferredLocale()] ?? $text[$tenant->locale] ?? (array_values(array_filter($text))[0] ?? null);
        }
        if (! $text && $product?->type === 'coaching_package') {
            $text = __('payments.welcome_message', ['name' => $client->firstName(), 'product' => (string) $product->translate('name', $client->preferredLocale()), 'coach' => $coach->firstName()], $client->preferredLocale());
        }
        if (! $text) {
            return null;
        }
        $text = strtr((string) $text, ['{client_name}' => $client->name, '{first_name}' => $client->firstName(), '{business_name}' => $tenant->name]);

        $conversation = $this->directConversation($tenant, $coach, $client);
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $coach->id, 'type' => 'text', 'body' => $text, 'meta' => ['source' => 'purchase_welcome']]);
        $conversation->forceFill(['last_message_at' => now()])->save();

        return $conversation->id;
    }

    private function directConversation(Tenant $tenant, User $coach, User $client): Conversation
    {
        if (class_exists(self::MESSAGING) && method_exists(self::MESSAGING, 'findOrCreateDirect')) {
            return app(self::MESSAGING)->findOrCreateDirect($tenant, $coach, $client);
        }
        $existing = Conversation::query()->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $coach->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $client->id))
            ->first();
        if ($existing) {
            return $existing;
        }
        $attributes = ['type' => 'direct', 'created_by' => $coach->id];
        if (Schema::hasColumn('conversations', 'direct_key')) {
            $ids = [$coach->id, $client->id];
            sort($ids);
            $attributes['direct_key'] = $tenant->id.'|'.implode('|', $ids);
        }
        $conversation = Conversation::create($attributes);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $coach->id, 'role' => 'owner']);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $client->id, 'role' => 'member']);

        return $conversation;
    }

    /** Revokes the content an order granted (expiry after grace, refund, failed renewals). */
    public function revoke(Order $order, string $reason = 'expiry'): void
    {
        $tenant = Tenant::findOrFail($order->tenant_id);
        $this->context->run($tenant, fn () => DB::transaction(function () use ($order, $reason) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->revoked_at) {
                return;
            }
            $g = $locked->meta['fulfilment'] ?? [];
            $today = now()->toDateString();

            if (! empty($g['program_assignment_id']) && class_exists(\App\Domain\Workouts\Models\ProgramAssignment::class)) {
                \App\Domain\Workouts\Models\ProgramAssignment::query()->whereKey($g['program_assignment_id'])->whereNotIn('status', ['completed'])->update(['status' => 'cancelled']);
            }
            if (! empty($g['meal_plan_id'])) {
                MealPlan::query()->whereKey($g['meal_plan_id'])->update(['status' => 'archived']);
            }
            if (! empty($g['habit_assignment_ids'])) {
                HabitAssignment::query()->whereIn('id', (array) $g['habit_assignment_ids'])->update(['is_active' => false, 'end_date' => $today]);
            }
            if (! empty($g['checkin_schedule_id'])) {
                CheckinSchedule::query()->whereKey($g['checkin_schedule_id'])->update(['is_active' => false]);
            }
            if (! empty($g['group_id']) && $locked->client_id && ($group = ClientGroup::query()->find($g['group_id']))) {
                $group->members()->detach($locked->client_id);
            }
            if (! empty($g['credits']) && $locked->client_id) {
                $remaining = min((int) $g['credits'], CreditLedgerEntry::balanceFor($locked->client_id));
                if ($remaining > 0) {
                    CreditLedgerEntry::create(['client_id' => $locked->client_id, 'amount' => -$remaining, 'reason' => $reason === 'refund' ? 'refund' : 'expiry', 'order_id' => $locked->id]);
                }
            }
            $locked->forceFill(['revoked_at' => now()])->save();
            ActivityLogger::log('payments.access_revoked', $locked, ['reason' => $reason], tenantId: $locked->tenant_id);
            Notify::user($locked->client, 'access_ended', ['product' => (string) ($locked->items()->first()?->description ?? '')]);
        }));
    }

    /** Scheduled: revoke access after expiry + the product's grace period. */
    public function expireAccess(): int
    {
        $count = 0;

        Order::withoutGlobalScopes()
            ->whereNull('revoked_at')
            ->whereNotNull('access_expires_at')
            ->where('access_expires_at', '<=', now())
            ->whereIn('status', ['paid', 'partially_refunded'])
            ->with('items')
            ->limit(500)
            ->get()
            ->each(function (Order $order) use (&$count) {
                $grace = (int) (Product::withoutGlobalScopes()->withTrashed()->whereKey($order->items->first()?->product_id)->value('grace_days') ?? 0);
                if ($order->access_expires_at->copy()->addDays($grace)->isFuture()) {
                    return;
                }
                $this->revoke($order);
                $count++;
            });

        ClientSubscription::withoutGlobalScopes()
            ->whereNull('revoked_at')
            ->whereIn('ended_reason', ['cancelled', 'payment_failed'])
            ->whereIn('status', ['cancelled', 'paused'])
            ->limit(500)
            ->get()
            ->each(function (ClientSubscription $sub) use (&$count) {
                $grace = (int) (Product::withoutGlobalScopes()->withTrashed()->whereKey($sub->product_id)->value('grace_days') ?? 0);
                $endedAt = $sub->ended_at ?? $sub->paused_at ?? $sub->current_period_end;
                if (! $endedAt || $endedAt->copy()->addDays($grace)->isFuture()) {
                    return;
                }
                $initial = $sub->order_id ? Order::withoutGlobalScopes()->find($sub->order_id) : null;
                if ($initial) {
                    $this->revoke($initial);
                }
                $sub->forceFill(['revoked_at' => now()])->save();
                $count++;
            });

        return $count;
    }
}
