<?php

namespace App\Domain\Ai\Services;

use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Identity\Models\User;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\WorkoutSession;

/**
 * Loads the structured client data prompts are grounded on, always scoped to
 * the active tenant, and the "Based on N check-ins and M sessions" citation.
 */
final class ClientDataService
{
    public const GROUNDING_DAYS = 84;

    /** A client of this tenant, or 404. */
    public function resolveClient(Tenant $tenant, string $clientId): User
    {
        $isClient = Membership::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $clientId)
            ->where('role', 'client')
            ->where('status', 'active')
            ->exists();
        if (! $isClient) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return User::query()->findOrFail($clientId);
    }

    public function profile(Tenant $tenant, User $client): ?ClientProfile
    {
        return ClientProfile::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $client->id)->first();
    }

    public function language(?User $client, ?string $requested = null, ?Tenant $tenant = null): string
    {
        $candidates = [$requested, $client?->locale, $tenant?->locale, 'en'];
        foreach ($candidates as $c) {
            if (in_array($c, ['en', 'ar', 'fr', 'es'], true)) {
                return $c;
            }
        }

        return 'en';
    }

    public function sessionCount(Tenant $tenant, User $client, int $days = self::GROUNDING_DAYS): int
    {
        return WorkoutSession::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('client_id', $client->id)
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->where('started_at', '>=', now()->subDays($days))
            ->count();
    }

    public function checkinCount(Tenant $tenant, User $client, int $days = self::GROUNDING_DAYS): int
    {
        return CheckinResponse::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('client_id', $client->id)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->where('submitted_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function grounding(Tenant $tenant, ?User $client, array $extra = []): array
    {
        $checkins = $client ? $this->checkinCount($tenant, $client) : 0;
        $sessions = $client ? $this->sessionCount($tenant, $client) : 0;

        return [
            'checkins' => $checkins,
            'sessions' => $sessions,
            'window_days' => self::GROUNDING_DAYS,
            'label' => __('ai.grounding', ['checkins' => $checkins, 'sessions' => $sessions]),
        ] + $extra;
    }

    /** @return list<array{date: string, weight_kg: float}> */
    public function weights(Tenant $tenant, User $client, int $limit = 12): array
    {
        return BodyMetric::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('client_id', $client->id)
            ->whereNotNull('weight_kg')
            ->orderByDesc('measured_on')
            ->limit($limit)
            ->get(['measured_on', 'weight_kg'])
            ->reverse()
            ->map(fn (BodyMetric $m) => ['date' => $m->measured_on->toDateString(), 'weight_kg' => (float) $m->weight_kg])
            ->values()
            ->all();
    }

    /**
     * The minimum profile a prompt needs: first name only, no contact data.
     *
     * @return array<string, mixed>
     */
    public function promptProfile(Tenant $tenant, User $client): array
    {
        $profile = $this->profile($tenant, $client);

        return [
            'first_name' => $client->firstName(),
            'goal' => $profile?->goal,
            'level' => $profile?->level,
            'gender' => $profile?->gender,
            'age' => $profile?->age(),
            'height_cm' => $profile?->height_cm,
            'activity_level' => $profile?->activity_level,
            'equipment' => $profile?->equipment ?? [],
            'days_per_week' => $profile?->days_per_week,
            'diet_tags' => $profile?->diet_tags ?? [],
            'injuries' => $profile?->injuries,
            'parq_flagged' => (bool) $profile?->parq_flagged,
        ];
    }
}
