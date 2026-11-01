<?php

namespace App\Domain\Marketing\Services;

use App\Domain\Crm\Models\Lead;
use App\Domain\Crm\Models\LeadActivity;
use App\Domain\Crm\Models\LeadStage;
use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Services\OutgoingWebhooks;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\Marketplace\NewMarketplaceLead;
use Illuminate\Support\Facades\DB;

/**
 * Turns a public enquiry (profile contact form or match-quiz introduction)
 * into a CRM lead inside the coach's tenant, then notifies the coach and
 * emits the lead.created webhook.
 */
final class MarketplaceLeadService
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly OutgoingWebhooks $webhooks,
    ) {}

    /**
     * @param  array{name: string, email?: string|null, phone?: string|null, message?: string|null, goal?: string|null, consent: bool}  $data
     * @param  'profile'|'match'  $origin
     */
    public function create(CoachProfile $profile, array $data, string $origin = 'profile', ?User $requester = null): Lead
    {
        $tenant = $profile->tenant ?? Tenant::query()->findOrFail($profile->tenant_id);

        $lead = $this->context->run($tenant, function () use ($tenant, $profile, $data, $origin, $requester) {
            return DB::transaction(function () use ($tenant, $profile, $data, $origin, $requester) {
                $email = isset($data['email']) ? mb_strtolower(trim((string) $data['email'])) : null;
                $message = trim((string) ($data['message'] ?? '')) ?: null;

                // Repeated submissions within a day update the open lead instead of duplicating it.
                $existing = $email ? Lead::query()->where('email', $email)->whereNull('converted_at')
                    ->where('created_at', '>=', now()->subDay())->first() : null;
                if ($existing) {
                    $existing->update(['message' => $message ?? $existing->message, 'phone' => $data['phone'] ?? $existing->phone]);

                    return $existing;
                }

                $stageId = LeadStage::query()->where('kind', 'open')->orderBy('position')->value('id');
                $lead = Lead::create([
                    'tenant_id' => $tenant->id,
                    'lead_stage_id' => $stageId,
                    'owner_id' => $profile->user_id,
                    'name' => trim((string) $data['name']),
                    'email' => $email,
                    'phone' => $data['phone'] ?? null,
                    'source' => $origin === 'match' ? 'marketplace' : 'profile',
                    'message' => $message,
                    'tags' => array_values(array_filter(['marketplace', $origin === 'match' ? 'match_quiz' : 'profile', $data['goal'] ?? null])),
                    'consent' => (bool) $data['consent'],
                ]);

                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $requester?->id,
                    'type' => 'note',
                    'body' => __('marketplace.lead_activity.'.($origin === 'match' ? 'match' : 'profile'), [], $tenant->locale ?: 'en'),
                ]);

                ActivityLogger::log('marketplace.lead_created', $lead, ['origin' => $origin, 'coach_profile_id' => $profile->id], causerId: $requester?->id, tenantId: $tenant->id);

                return $lead;
            });
        });

        if ($lead->wasRecentlyCreated) {
            $profile->user?->notify(new NewMarketplaceLead($lead->id, $tenant->id, $lead->name, $origin));
            $this->webhooks->dispatch($tenant, 'lead.created', [
                'lead' => [
                    'id' => $lead->id, 'name' => $lead->name, 'email' => $lead->email, 'phone' => $lead->phone,
                    'source' => $lead->source, 'message' => $lead->message, 'tags' => $lead->tags, 'consent' => $lead->consent,
                    'created_at' => $lead->created_at?->toIso8601String(),
                ],
                'coach' => ['id' => $profile->user_id, 'profile_slug' => $profile->slug],
            ]);
        }

        return $lead;
    }
}
