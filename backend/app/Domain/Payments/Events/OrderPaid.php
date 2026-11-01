<?php

namespace App\Domain\Payments\Events;

use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;

/** A client order was paid (online, manual or free). Triggers the FulfilOrder job. */
final class OrderPaid
{
    use Dispatchable;

    public function __construct(public readonly Order $order, public readonly ?Payment $payment = null) {}
}
