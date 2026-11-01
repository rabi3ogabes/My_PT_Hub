<?php

namespace App\Domain\Integrations\Listeners;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Services\OutgoingWebhooks;
use App\Domain\Payments\Events\OrderPaid;
use App\Domain\Tenancy\Events\ClientJoined;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Events\WorkoutCompleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Maps domain events to public webhook events and fans them out to the
 * tenant's endpoints. Event classes are optional (registered only when they
 * exist), so payloads are built defensively from the event's public models.
 */
class ForwardDomainEventToWebhooks
{
    /** Domain event class => public webhook event name. */
    public const MAP = [
        ClientJoined::class => 'client.created',
        WorkoutCompleted::class => 'workout.completed',
        OrderPaid::class => 'payment.succeeded',
        'App\Domain\Payments\Events\PaymentSucceeded' => 'payment.succeeded',
        'App\Domain\Checkins\Events\CheckinSubmitted' => 'checkin.submitted',
        'App\Domain\Crm\Events\LeadCreated' => 'lead.created',
        'App\Domain\Calendar\Events\BookingCreated' => 'booking.created',
        'App\Domain\Calendar\Events\BookingCancelled' => 'booking.cancelled',
    ];

    /** Attributes never sent to third parties. */
    private const SENSITIVE = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'access_token', 'refresh_token', 'secret', 'date_of_birth', 'injuries', 'medical_notes', 'checkout_url', 'idempotency_key'];

    public function __construct(private readonly OutgoingWebhooks $webhooks) {}

    public function handle(object $event): void
    {
        $name = self::MAP[$event::class] ?? null;
        if ($name === null) {
            return;
        }

        try {
            $tenant = $this->tenantOf($event);
            if (! $tenant) {
                return;
            }
            $this->webhooks->dispatch($tenant, $name, $this->payload($name, $event));
        } catch (Throwable $e) {
            Log::error('integrations.webhook_forward_failed', ['event' => $event::class, 'error' => $e->getMessage()]);
        }
    }

    /** @return array<string, mixed> */
    public function payload(string $name, object $event): array
    {
        return match ($name) {
            'client.created' => [
                'client' => $this->userPayload($event->client ?? null),
                'joined_at' => now()->toIso8601String(),
            ],
            'workout.completed' => $this->workoutPayload($event),
            'payment.succeeded' => $this->paymentPayload($event),
            default => $this->genericPayload($event),
        };
    }

    private function tenantOf(object $event): ?Tenant
    {
        foreach (get_object_vars($event) as $value) {
            if ($value instanceof Tenant) {
                return $value;
            }
        }
        foreach (get_object_vars($event) as $value) {
            if ($value instanceof Model && $value->getAttribute('tenant_id')) {
                return Tenant::query()->find($value->getAttribute('tenant_id'));
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    private function userPayload(?Model $user): ?array
    {
        return $user ? ['id' => $user->getKey(), 'name' => $user->getAttribute('name'), 'email' => $user->getAttribute('email'), 'phone' => $user->getAttribute('phone')] : null;
    }

    /** @return array<string, mixed> */
    private function workoutPayload(object $event): array
    {
        $session = $event->session ?? null;
        if (! $session instanceof Model) {
            return $this->genericPayload($event);
        }
        $client = $session->getAttribute('client_id') ? User::query()->find($session->getAttribute('client_id')) : null;
        $date = fn (string $key) => $session->getAttribute($key) instanceof \DateTimeInterface ? $session->getAttribute($key)->format(DATE_ATOM) : $session->getAttribute($key);

        return [
            'session_id' => $session->getKey(),
            'client' => $this->userPayload($client),
            'workout_id' => $session->getAttribute('workout_id'),
            'name' => $session->getAttribute('name'),
            'started_at' => $date('started_at'),
            'ended_at' => $date('ended_at'),
            'duration_seconds' => $session->getAttribute('duration_seconds'),
            'total_volume_kg' => $session->getAttribute('total_volume_kg') !== null ? (float) $session->getAttribute('total_volume_kg') : null,
            'personal_records' => is_array($event->records ?? null) ? count($event->records) : 0,
        ];
    }

    /** @return array<string, mixed> */
    private function paymentPayload(object $event): array
    {
        $order = $event->order ?? null;
        if (! $order instanceof Model) {
            return $this->genericPayload($event);
        }
        $paidAt = $order->getAttribute('paid_at');

        return [
            'order_id' => $order->getKey(),
            'client_id' => $order->getAttribute('client_id'),
            'customer_email' => $order->getAttribute('customer_email'),
            'customer_name' => $order->getAttribute('customer_name'),
            'amount_minor' => (int) $order->getAttribute('total_minor'),
            'currency' => $order->getAttribute('currency'),
            'status' => $order->getAttribute('status'),
            'paid_at' => $paidAt instanceof \DateTimeInterface ? $paidAt->format(DATE_ATOM) : $paidAt,
        ];
    }

    /** @return array<string, mixed> */
    private function genericPayload(object $event): array
    {
        $out = [];
        foreach (get_object_vars($event) as $key => $value) {
            if ($value instanceof Model) {
                $out[$key] = Arr::except($value->attributesToArray(), self::SENSITIVE);
            } elseif (is_scalar($value) || $value === null) {
                $out[$key] = $value;
            }
        }

        return $out;
    }
}
