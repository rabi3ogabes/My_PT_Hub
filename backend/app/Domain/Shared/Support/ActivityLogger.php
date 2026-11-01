<?php

namespace App\Domain\Shared\Support;

use App\Domain\Identity\Models\ActivityLog;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;

/** Audit trail for sensitive actions (logins, impersonation, billing, exports, deletions). */
final class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(string $event, ?Model $subject = null, array $properties = [], ?string $causerId = null, ?string $tenantId = null): ActivityLog
    {
        $request = app()->runningInConsole() ? null : request();

        return ActivityLog::create([
            'tenant_id' => $tenantId ?? app(TenantContext::class)->id(),
            'causer_id' => $causerId ?? $request?->user()?->getAuthIdentifier(),
            'impersonator_id' => $request?->hasSession() ? $request->session()->get('impersonator_id') : null,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            'created_at' => now(),
        ]);
    }
}
