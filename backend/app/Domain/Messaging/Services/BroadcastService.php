<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\ScheduledMessage;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Log;

/**
 * Scheduled messages / broadcasts: each recipient receives the body rendered
 * in their own locale inside their direct conversation with the sender.
 */
final class BroadcastService
{
    public function __construct(
        private readonly SegmentService $segments,
        private readonly TemplateRenderer $renderer,
        private readonly MessagingService $messaging,
        private readonly TenantContext $context,
    ) {}

    /**
     * @param  array<string, mixed>  $segment
     * @param  array<string, string>  $body  {locale: text}
     */
    public function schedule(Tenant $tenant, User $sender, array $segment, array $body, ?\DateTimeInterface $sendAt = null): ScheduledMessage
    {
        $scheduled = $this->context->run($tenant, fn () => ScheduledMessage::create([
            'tenant_id' => $tenant->id,
            'sender_id' => $sender->id,
            'segment' => $segment,
            'body' => $body,
            'send_at' => $sendAt ?? now(),
        ]));

        if ($scheduled->send_at->lessThanOrEqualTo(now())) {
            $this->dispatch($scheduled);
        }

        return $scheduled->refresh();
    }

    /** Sends a scheduled message exactly once (atomic claim on sent_at). Returns the recipient count. */
    public function dispatch(ScheduledMessage $scheduled): int
    {
        $claimed = ScheduledMessage::withoutGlobalScopes()->whereKey($scheduled->id)->whereNull('sent_at')->update(['sent_at' => now()]);
        if ($claimed === 0) {
            return 0;
        }
        $tenant = Tenant::query()->findOrFail($scheduled->tenant_id);
        $sender = User::query()->find($scheduled->sender_id);
        if (! $sender) {
            return 0;
        }

        return $this->context->run($tenant, function () use ($scheduled, $tenant, $sender) {
            $count = 0;
            foreach ($this->segments->resolve($tenant, $scheduled->segment ?? []) as $recipient) {
                if ($recipient->id === $sender->id) {
                    continue;
                }
                try {
                    $conversation = $this->messaging->findOrCreateDirect($tenant, $sender, $recipient);
                    $this->messaging->send($conversation, $sender, [
                        'type' => 'text',
                        'body' => $this->renderer->render($scheduled->body ?? [], $recipient, $tenant),
                        'meta' => ['broadcast_id' => $scheduled->id],
                    ]);
                    $count++;
                } catch (\Throwable $e) {
                    Log::warning('messaging.broadcast_recipient_failed', ['scheduled_message' => $scheduled->id, 'recipient' => $recipient->id, 'error' => $e->getMessage()]);
                }
            }
            ScheduledMessage::query()->whereKey($scheduled->id)->update(['recipients_count' => $count]);

            return $count;
        });
    }

    /** Dispatches every due scheduled message across tenants. */
    public function dispatchDue(): int
    {
        $sent = 0;
        ScheduledMessage::withoutGlobalScopes()->whereNull('sent_at')->where('send_at', '<=', now())->orderBy('send_at')
            ->each(function (ScheduledMessage $m) use (&$sent) {
                $sent += $this->dispatch($m);
            });

        return $sent;
    }
}
