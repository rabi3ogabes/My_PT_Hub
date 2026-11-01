<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Models\Order;
use Illuminate\Http\Request;

/** Drivers that confirm the payment when the shopper is redirected back (status lookup / capture). */
interface HandlesReturn
{
    public function resultFromReturn(Order $order, Request $request): ?WebhookResult;
}
