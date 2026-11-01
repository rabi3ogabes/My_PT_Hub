<?php

namespace App\Http\Api\V1\Identity;

use App\Domain\Identity\Jobs\DeleteAccount;
use App\Domain\Identity\Jobs\ExportUserData;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantProvisioner;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\TenantResource;
use App\Http\Api\V1\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountController extends ApiController
{
    public function avatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096']]);
        $user = $this->user();
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->update(['avatar_path' => $request->file('avatar')->store('avatars', 'public')]);

        return $this->ok((new UserResource($user))->resolve());
    }

    /** GDPR / PDPPL data export: a zip of JSON + media, emailed as a signed link. */
    public function export(): JsonResponse
    {
        ExportUserData::dispatch($this->user()->id);
        ActivityLogger::log('account.export_requested', $this->user());

        return $this->ok(['queued' => true]);
    }

    /** Right to erasure. Owners of a business with other members must transfer ownership first. */
    public function destroy(Request $request): JsonResponse
    {
        $user = $this->user();
        $request->validate(['password' => [$user->password ? 'required' : 'nullable', 'current_password:sanctum'], 'confirm' => ['required', 'accepted']]);

        foreach ($user->memberships()->where('role', 'owner')->where('status', 'active')->get() as $m) {
            $tenant = Tenant::find($m->tenant_id);
            if ($tenant && ! $tenant->isPersonal() && $tenant->memberships()->where('status', 'active')->where('user_id', '!=', $user->id)->exists()) {
                throw new ApiException('transfer_ownership_first', __('clients_api.transfer_ownership_first', ['business' => $tenant->name]), 409);
            }
        }

        ActivityLogger::log('account.deletion_requested', $user);
        $user->tokens()->delete();
        DeleteAccount::dispatch($user->id);

        return $this->ok(['scheduled' => true]);
    }

    public function createTenant(Request $request, TenantProvisioner $provisioner): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'currency' => ['nullable', 'string', 'size:3'],
            'country' => ['nullable', 'string', 'size:2'],
            'timezone' => ['nullable', 'timezone:all'],
            'locale' => ['nullable', 'in:en,ar,fr,es'],
        ]);
        $tenant = $provisioner->provision($this->user(), array_filter($data));

        return $this->created((new TenantResource($tenant))->resolve() + ['role' => 'owner']);
    }

    public function switchTenant(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant_id' => ['required', 'string']]);
        $user = $this->user();
        if (! $user->membershipFor($data['tenant_id'])) {
            throw ApiException::forbidden(__('api.not_a_member'));
        }
        $user->forceFill(['current_tenant_id' => $data['tenant_id']])->save();

        return $this->ok(['current_tenant_id' => $data['tenant_id']]);
    }
}
