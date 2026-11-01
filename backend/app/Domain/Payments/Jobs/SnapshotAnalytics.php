<?php

namespace App\Domain\Payments\Jobs;

use App\Domain\Payments\Services\AnalyticsService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/** Materialises analytics_daily for every business tenant (default: yesterday in UTC). */
final class SnapshotAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly ?string $date = null) {}

    public function handle(AnalyticsService $analytics): void
    {
        $date = $this->date ? Carbon::parse($this->date) : now()->subDay();
        Tenant::query()->where('type', 'business')->select('id')->chunkById(200, function ($tenants) use ($analytics, $date) {
            foreach ($tenants as $row) {
                $tenant = Tenant::find($row->id);
                if ($tenant) {
                    $analytics->snapshot($tenant, $date);
                }
            }
        });
    }
}
