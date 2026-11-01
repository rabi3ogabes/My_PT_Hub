<?php

namespace App\Domain\Marketing\Jobs;

use App\Domain\Marketing\Services\ReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Sends due review requests (clients 30+ days after joining, asked once per business). */
class SendReviewRequestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public function handle(ReviewService $reviews): void
    {
        $reviews->sendDueRequests();
    }
}
