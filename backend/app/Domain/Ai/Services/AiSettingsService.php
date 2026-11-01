<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\Ai\Support\AiFeatures;
use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Collection;

/**
 * Coach voice + AI switches. Each staff member has an ai_settings row; the
 * owners' rows are tenant-wide: an owner opting out disables AI for the whole
 * business, and features an owner disables are off for everyone. Client-side
 * tools are toggled in tenants.settings.ai.
 */
final class AiSettingsService
{
    public const TONES = ['motivational', 'technical', 'nurturing', 'tough_love'];

    public function forUser(Tenant $tenant, User $user): AiSetting
    {
        return AiSetting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->first()
            ?? new AiSetting([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'tone' => 'motivational',
                'use_emoji' => true,
                'banned_words' => [],
                'sample_replies' => [],
                'disabled_features' => [],
                'opt_out' => false,
            ]);
    }

    /** Voice settings used when drafting on behalf of a client's coach. */
    public function voiceFor(Tenant $tenant, ?User $staff, ?string $clientCoachId = null): AiSetting
    {
        if ($staff && $staff->isStaffIn($tenant->id)) {
            return $this->forUser($tenant, $staff);
        }
        $coach = $clientCoachId ? User::query()->find($clientCoachId) : null;
        $coach ??= $this->owners($tenant)->first();

        return $coach ? $this->forUser($tenant, $coach) : new AiSetting(['tone' => 'motivational', 'use_emoji' => true]);
    }

    public function tenantOptedOut(Tenant $tenant): bool
    {
        if ((bool) $tenant->setting('ai.opt_out', false)) {
            return true;
        }

        return $this->ownerSettings($tenant)->contains(fn (AiSetting $s) => $s->opt_out);
    }

    public function userOptedOut(Tenant $tenant, ?User $user): bool
    {
        return $user !== null && (bool) $this->forUser($tenant, $user)->opt_out;
    }

    /** @return list<string> */
    public function disabledFeatures(Tenant $tenant, ?User $user = null): array
    {
        $disabled = $this->ownerSettings($tenant)->flatMap(fn (AiSetting $s) => $s->disabled_features ?? [])->all();
        if ($user && $user->isStaffIn($tenant->id)) {
            array_push($disabled, ...($this->forUser($tenant, $user)->disabled_features ?? []));
        }

        return array_values(array_unique($disabled));
    }

    public function clientToolEnabled(Tenant $tenant, string $feature): bool
    {
        if ($tenant->isPersonal()) {
            return true;
        }
        [$key, $default] = AiFeatures::CLIENT_TOOL_SETTINGS[$feature] ?? [null, false];

        return $key !== null && (bool) $tenant->setting("ai.{$key}", $default);
    }

    /** @return array<string, bool> */
    public function clientTools(Tenant $tenant): array
    {
        $out = [];
        foreach (AiFeatures::CLIENT_TOOL_SETTINGS as [$key, $default]) {
            $out[$key] = $tenant->isPersonal() ? true : (bool) $tenant->setting("ai.{$key}", $default);
        }

        return $out;
    }

    /** @param array<string, bool> $tools */
    public function updateClientTools(Tenant $tenant, array $tools): void
    {
        $settings = $tenant->settings ?? [];
        foreach ($tools as $key => $enabled) {
            data_set($settings, "ai.{$key}", (bool) $enabled);
        }
        $tenant->forceFill(['settings' => $settings])->save();
    }

    /** @return Collection<int, User> */
    public function owners(Tenant $tenant): Collection
    {
        $ids = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'owner')->where('status', 'active')->pluck('user_id');

        return User::query()->whereIn('id', $ids)->get();
    }

    /** @return Collection<int, AiSetting> */
    private function ownerSettings(Tenant $tenant): Collection
    {
        $ids = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'owner')->where('status', 'active')->pluck('user_id');

        return AiSetting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->whereIn('user_id', $ids)->get();
    }
}
