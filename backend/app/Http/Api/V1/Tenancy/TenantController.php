<?php

namespace App\Http\Api\V1\Tenancy;

use App\Domain\Billing\Services\PlanGate;
use App\Domain\Shared\Support\ActivityLogger;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TenantController extends ApiController
{
    public function show(PlanGate $gate): JsonResponse
    {
        $tenant = $this->tenant();
        $plan = $gate->plan($tenant);
        $isStaff = $this->user()->isStaffIn();

        return $this->ok(array_merge((new TenantResource($tenant))->resolve(), [
            'role' => $this->user()->roleIn(),
            'features' => $plan->features ?? [],
            'limits' => $plan->limits ?? [],
            'settings' => $isStaff ? (object) ($tenant->settings ?? []) : (object) [],
            'legal' => $this->user()->isOwnerIn() ? (object) ($tenant->legal ?? []) : (object) [],
            'usage' => $isStaff ? ['clients' => $tenant->clientCount(), 'staff' => $tenant->staffCount()] : null,
        ]));
    }

    public function update(Request $request): JsonResponse
    {
        $tenant = $this->tenant();
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'brand_color' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'country' => ['sometimes', 'nullable', 'string', 'size:2'],
            'timezone' => ['sometimes', 'timezone:all'],
            'locale' => ['sometimes', Rule::in(['en', 'ar', 'fr', 'es'])],
            'onboarded' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'array'],
            'settings.compliance_weights' => ['sometimes', 'array'],
            'settings.compliance_weights.*' => ['integer', 'between:0,100'],
            'settings.ai' => ['sometimes', 'array'],
            'settings.ai.*' => ['boolean'],
            'legal' => ['sometimes', 'array'],
            'legal.legal_name' => ['nullable', 'string', 'max:190'],
            'legal.vat_number' => ['nullable', 'string', 'max:40'],
            'legal.cr_number' => ['nullable', 'string', 'max:40'],
            'legal.address' => ['nullable', 'string', 'max:500'],
            'legal.email' => ['nullable', 'email'],
            'legal.phone' => ['nullable', 'string', 'max:32'],
        ]);
        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }
        if (array_key_exists('onboarded', $data)) {
            $tenant->onboarded_at = $data['onboarded'] ? ($tenant->onboarded_at ?? now()) : null;
            unset($data['onboarded']);
        }
        if (isset($data['settings'])) {
            $data['settings'] = array_replace_recursive($tenant->settings ?? [], $data['settings']);
        }
        if (isset($data['legal'])) {
            $data['legal'] = array_replace($tenant->legal ?? [], $data['legal']);
        }
        $tenant->fill($data)->save();
        ActivityLogger::log('tenant.updated', $tenant, ['fields' => array_keys($data)]);

        return $this->show(app(PlanGate::class));
    }

    public function logo(Request $request): JsonResponse
    {
        $request->validate(['logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048']]);
        $tenant = $this->tenant();
        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
        }
        $tenant->update(['logo_path' => $request->file('logo')->store('tenants/'.$tenant->id, 'public')]);

        return $this->ok(['logo_url' => Storage::disk('public')->url($tenant->logo_path)]);
    }
}
