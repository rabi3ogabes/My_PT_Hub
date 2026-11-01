<?php

namespace App\Providers\Modules;

use App\Domain\Crm\Models\Lead;
use App\Domain\Payments\Events\OrderPaid;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Jobs\FulfilOrder;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Models\Product;
use App\Domain\Payments\Models\Refund;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/** Phase 5 — client payments, analytics and CRM. */
class PaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GatewayManager::class);
    }

    public function boot(): void
    {
        // Audit-log subjects owned by this module (merged into the enforced morph map).
        Relation::morphMap([
            'payment_gateway' => PaymentGateway::class,
            'product' => Product::class,
            'invoice' => Invoice::class,
            'refund' => Refund::class,
            'lead' => Lead::class,
        ]);

        Event::listen(OrderPaid::class, function (OrderPaid $event): void {
            FulfilOrder::dispatch($event->order->id)->afterCommit();
        });
    }
}
