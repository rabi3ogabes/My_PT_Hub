<?php

namespace App\Domain\Payments\Events;

use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Refund;
use Illuminate\Foundation\Events\Dispatchable;

final class OrderRefunded
{
    use Dispatchable;

    public function __construct(public readonly Order $order, public readonly Refund $refund) {}
}
