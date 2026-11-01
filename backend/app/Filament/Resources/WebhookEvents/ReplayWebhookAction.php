<?php

namespace App\Filament\Resources\WebhookEvents;

use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Payments\Models\WebhookEvent;
use App\Domain\Shared\Support\ActivityLogger;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * stripe_saas events are re-processed synchronously through the (idempotent)
 * SaasBillingService::handleWebhook. Other drivers belong to their module's
 * processors: the event is reset to "received" so it is picked up again.
 */
final class ReplayWebhookAction
{
    public static function replay(WebhookEvent $event): bool
    {
        $event->update(['status' => 'received', 'error' => null, 'processed_at' => null]);

        if ($event->driver !== 'stripe_saas') {
            ActivityLogger::log('admin.webhook.retry_marked', null, ['webhook_event_id' => $event->id, 'driver' => $event->driver, 'type' => $event->type], tenantId: $event->tenant_id);

            return true;
        }

        $payload = $event->payload ?? [];
        $payload['id'] = $event->external_id;

        try {
            app(SaasBillingService::class)->handleWebhook($payload);
        } catch (\Throwable $e) {
            report($e);
            ActivityLogger::log('admin.webhook.replay_failed', null, ['webhook_event_id' => $event->id, 'error' => $e->getMessage()], tenantId: $event->tenant_id);

            return false;
        }

        ActivityLogger::log('admin.webhook.replayed', null, ['webhook_event_id' => $event->id, 'type' => $event->type], tenantId: $event->tenant_id);

        return true;
    }

    public static function make(): Action
    {
        return Action::make('replay')
            ->label(__('Replay'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(fn (WebhookEvent $record): string => $record->driver === 'stripe_saas'
                ? __('Re-runs processing now through the SaaS billing service (idempotent).')
                : __('Marks the event for retry by its module processor.'))
            ->action(function (WebhookEvent $record): void {
                $ok = self::replay($record);
                $record->refresh();
                $ok
                    ? Notification::make()->title($record->driver === 'stripe_saas' ? __('Webhook replayed: :status', ['status' => $record->status]) : __('Webhook marked for retry'))->success()->send()
                    : Notification::make()->title(__('Replay failed'))->body((string) $record->error)->danger()->send();
            });
    }
}
