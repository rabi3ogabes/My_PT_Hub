<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Crm\Models\LeadStage;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\CoachProfile;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Creates a coaching business with sensible defaults so the coach lands in a ready workspace. */
final class TenantProvisioner
{
    public function __construct(
        private readonly MembershipService $memberships,
        private readonly TenantContext $context,
        private readonly SaasBillingService $billing,
    ) {}

    /**
     * @param  array{name?: string, currency?: string, country?: string, timezone?: string, locale?: string, brand_color?: string, type?: string}  $data
     */
    public function provision(User $owner, array $data = []): Tenant
    {
        return DB::transaction(function () use ($owner, $data) {
            $type = $data['type'] ?? 'business';
            $name = $data['name'] ?? ($type === 'personal' ? $owner->name : __('onboarding.default_business', ['name' => $owner->firstName()]));

            $tenant = Tenant::create([
                'type' => $type,
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'currency' => strtoupper($data['currency'] ?? 'USD'),
                'country' => $data['country'] ?? null,
                'timezone' => $data['timezone'] ?? $owner->timezone ?? 'UTC',
                'locale' => $data['locale'] ?? $owner->locale ?? 'en',
                'brand_color' => $data['brand_color'] ?? '#D4AF6A',
                'plan' => 'free',
                'settings' => [
                    'compliance_weights' => ['workouts' => 40, 'nutrition' => 25, 'habits' => 20, 'checkins' => 15],
                    'ai' => ['client_photo_logging' => true, 'self_guided_chat' => $type === 'personal'],
                ],
            ]);

            if ($type === 'personal') {
                // Standalone client: owns a personal workspace and is its own client.
                $this->memberships->add($tenant, $owner, 'client', enforceLimits: false);
                $owner->forceFill(['current_tenant_id' => $tenant->id])->save();

                return $tenant;
            }

            $this->memberships->add($tenant, $owner, 'owner', enforceLimits: false);
            $owner->forceFill(['current_tenant_id' => $tenant->id])->save();

            $this->context->run($tenant, function () use ($tenant, $owner) {
                CoachProfile::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $owner->id,
                    'slug' => $this->uniqueCoachSlug($owner->name),
                    'languages' => [$tenant->locale],
                ]);

                foreach ([['new', 'open'], ['contacted', 'open'], ['consultation_booked', 'open'], ['proposal', 'open'], ['won', 'won'], ['lost', 'lost']] as $i => [$key, $kind]) {
                    LeadStage::create(['name' => __("crm.stage.{$key}"), 'position' => $i, 'kind' => $kind]);
                }

                CheckinForm::create([
                    'name' => __('checkins.default_form'),
                    'questions' => DefaultContent::weeklyCheckinQuestions(),
                ]);

                CommunitySpace::create(['name' => __('community.default_space', ['name' => $tenant->name])]);
            });

            $this->billing->startTrial($tenant, 'pro');

            return $tenant->refresh();
        });
    }

    public function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'coach';
        $slug = $base;
        $i = 1;
        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function uniqueCoachSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'coach';
        $slug = $base;
        $i = 1;
        while (CoachProfile::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
