<?php

namespace App\Domain\Payments\Jobs;

use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Services\FulfilmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Delivers a paid order (content, credits, group, welcome message, coach notification). Idempotent. */
final class FulfilOrder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [5, 30, 120, 600];

    public int $uniqueFor = 300;

    public function __construct(public readonly string $orderId)
    {
        $this->onQueue('payments');
    }

    public function uniqueId(): string
    {
        return $this->orderId;
    }

    public function handle(FulfilmentService $fulfilment): void
    {
        $order = Order::withoutGlobalScopes()->find($this->orderId);
        if ($order) {
            $fulfilment->fulfil($order);
        }
    }
}
